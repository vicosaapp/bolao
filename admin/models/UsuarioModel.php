<?php
class UsuarioModel {
    private $conn;

    public function __construct() {
        try {
            // Incluir arquivo de conexão com o banco de dados
            error_log("[UsuarioModel] Iniciando construtor UsuarioModel");
            
            // Verificar se já existe uma conexão global
            global $conn;
            if (isset($conn) && ($conn instanceof mysqli) && !$conn->connect_error) {
                error_log("[UsuarioModel] Usando conexão global existente");
                $this->conn = $conn;
                return;
            }
            
            // Tentar arquivo principal /config/database.php
            $database_path = dirname(__DIR__, 2) . '/config/database.php';
            error_log("[UsuarioModel] Tentando usar arquivo: " . $database_path);
            
            if (file_exists($database_path)) {
                require_once $database_path;
                
                if (isset($conn) && ($conn instanceof mysqli) && !$conn->connect_error) {
                    error_log("[UsuarioModel] Conexão bem-sucedida via /config/database.php");
                    $this->conn = $conn;
                    return;
                }
            }
            
            // Tentar arquivo alternativo /includes/db_config.php
            $alternate_path = dirname(__DIR__, 2) . '/includes/db_config.php';
            error_log("[UsuarioModel] Tentando usar arquivo alternativo: " . $alternate_path);
            
            if (file_exists($alternate_path)) {
                require_once $alternate_path;
                
                if (isset($conn) && ($conn instanceof mysqli) && !$conn->connect_error) {
                    error_log("[UsuarioModel] Conexão bem-sucedida via /includes/db_config.php");
                    $this->conn = $conn;
                    return;
                }
            }
            
            // Se chegou aqui, tentar criar uma conexão direta
            error_log("[UsuarioModel] Tentando criar conexão direta");
            
            $host = 'localhost';
            $usuario = 'root';
            $senha = '';
            $banco = 'bolao_db';
            
            $this->conn = new mysqli($host, $usuario, $senha, $banco);
            
            if ($this->conn->connect_error) {
                throw new Exception("Erro na conexão direta com o banco de dados: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            error_log("[UsuarioModel] Conexão direta estabelecida com sucesso");
            
        } catch (Exception $e) {
            error_log("[UsuarioModel] ERRO no construtor: " . $e->getMessage());
            throw $e; // Repassar a exceção
        }
    }

    /**
     * Listar todos os usuários
     * @return array Lista de usuários
     */
    public function listarUsuarios() {
        $usuarios = [];
        $query = "SELECT id, nome, email, tipo AS nivel, status FROM usuarios ORDER BY id DESC";
        $result = $this->conn->query($query);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Mapear o tipo para o nível de acesso
                $niveis = [
                    'admin' => 4,     // Super Admin
                    'operador' => 3   // Administrador
                ];
                $row['nivel'] = $niveis[$row['nivel']] ?? 1;
                $usuarios[] = $row;
            }
        }

        return $usuarios;
    }

    /**
     * Adicionar novo usuário
     * @param string $nome Nome do usuário
     * @param string $email Email do usuário
     * @param string $senha Senha do usuário
     * @param int $nivel Nível de acesso
     * @param int $status Status do usuário (1=ativo, 0=inativo)
     * @return int|false ID do usuário inserido ou false em caso de erro
     */
    public function adicionarUsuario($nome, $email, $senha, $nivel, $status) {
        // Mapear nível para tipo de usuário
        $tipos = [
            1 => 'operador',  // Apostador
            2 => 'operador',  // Revendedor
            3 => 'operador',  // Administrador
            4 => 'admin'      // Super Admin
        ];
        $tipo = $tipos[$nivel] ?? 'operador';

        // Preparar dados
        $nome = $this->conn->real_escape_string($nome);
        $email = $this->conn->real_escape_string($email);
        $usuario = strtolower(str_replace(' ', '_', $nome));
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $status_db = $status ? 'ativo' : 'inativo';

        // Construir query
        $query = "INSERT INTO usuarios (nome, usuario, email, senha, tipo, status) VALUES ('$nome', '$usuario', '$email', '$senha_hash', '$tipo', '$status_db')";
        
        if ($this->conn->query($query)) {
            return $this->conn->insert_id;
        }

        return false;
    }

    /**
     * Atualizar usuário
     * @param int $id ID do usuário
     * @param string $nome Nome do usuário
     * @param string $email Email do usuário
     * @param int $nivel Nível de acesso
     * @param int $status Status do usuário (1=ativo, 0=inativo)
     * @param string|null $senha Senha do usuário (opcional)
     * @return bool Sucesso da operação
     */
    public function atualizarUsuario($id, $nome, $email, $nivel, $status, $senha = null) {
        // Mapear nível para tipo de usuário
        $tipos = [
            1 => 'operador',  // Apostador
            2 => 'operador',  // Revendedor
            3 => 'operador',  // Administrador
            4 => 'admin'      // Super Admin
        ];
        $tipo = $tipos[$nivel] ?? 'operador';

        // Preparar dados
        $nome = $this->conn->real_escape_string($nome);
        $email = $this->conn->real_escape_string($email);
        $status_db = $status ? 'ativo' : 'inativo';

        // Construir query
        $query = "UPDATE usuarios SET nome = '$nome', email = '$email', tipo = '$tipo', status = '$status_db'";

        // Adicionar atualização de senha se fornecida
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $query .= ", senha = '$senha_hash'";
        }

        $query .= " WHERE id = $id";
        
        return $this->conn->query($query);
    }

    /**
     * Excluir usuário
     * @param int $id ID do usuário
     * @return bool Sucesso da operação
     */
    public function excluirUsuario($id) {
        $query = "DELETE FROM usuarios WHERE id = $id";
        return $this->conn->query($query);
    }

    /**
     * Buscar usuário por ID
     * @param int $id ID do usuário
     * @return array|null Dados do usuário ou null
     */
    public function buscarUsuarioPorId($id) {
        $query = "SELECT * FROM usuarios WHERE id = $id";
        $result = $this->conn->query($query);

        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Verificar credenciais de login
     * @param string $email Email do usuário
     * @param string $senha Senha do usuário
     * @return array|false Dados do usuário ou false
     */
    public function verificarLogin($email, $senha) {
        $email = $this->conn->real_escape_string($email);

        $query = "SELECT * FROM usuarios WHERE email = '$email'";
        $result = $this->conn->query($query);

        if ($result && $result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            // Verificar senha
            if (password_verify($senha, $usuario['senha'])) {
                // Remover senha antes de retornar
                unset($usuario['senha']);
                return $usuario;
            }
        }

        return false;
    }

    /**
     * Obter usuário por ID
     * @param int $id ID do usuário
     * @return array|null Dados do usuário ou null se não encontrado
     */
    public function obterUsuarioPorId($id) {
        // Validar entrada
        error_log("[UsuarioModel] Buscando usuário com ID: " . $id);
        
        if (!$id) {
            error_log("[UsuarioModel] ID de usuário inválido: " . $id);
            // Retornar array vazio em vez de null para evitar erros
            return [
                'id' => null,
                'nome' => '',
                'email' => '',
                'nivel_acesso' => 1,
                'status' => 0
            ];
        }
        
        // Verificar se a conexão está ativa
        if (!$this->conn || $this->conn->connect_error) {
            error_log("[UsuarioModel] Erro: conexão com banco de dados não está disponível. Tentando reconectar...");
            // Tentar reconectar
            try {
                // Incluir arquivo de conexão com o banco de dados
                if (file_exists(dirname(__DIR__, 2) . '/config/database.php')) {
                    require_once dirname(__DIR__, 2) . '/config/database.php';
                    if (isset($conn) && $conn instanceof mysqli) {
                        $this->conn = $conn;
                        error_log("[UsuarioModel] Conexão reestabelecida com sucesso.");
                    } else {
                        error_log("[UsuarioModel] Falha ao reestabelecer conexão: variável $conn não disponível.");
                    }
                }
            } catch (Exception $e) {
                error_log("[UsuarioModel] Erro ao tentar reconectar: " . $e->getMessage());
                return null;
            }
        }
        
        try {
            // Executar consulta
            $query = "SELECT id, nome, email, tipo, status FROM usuarios WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                error_log("[UsuarioModel] Erro ao preparar consulta: " . $this->conn->error);
                return null;
            }
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $usuario = $result->fetch_assoc();
                
                // Mapear tipo para nível de acesso
                $niveis = [
                    'admin' => 4,     // Super Admin
                    'operador' => 3   // Administrador
                ];
                
                // Formatar resposta
                return [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email'],
                    'nivel_acesso' => $niveis[$usuario['tipo']] ?? 1,
                    'status' => ($usuario['status'] == 'ativo') ? 1 : 0
                ];
            } else {
                error_log("[UsuarioModel] Usuário não encontrado com ID: " . $id);
                return null;
            }
            
        } catch (Exception $e) {
            error_log("[UsuarioModel] Erro ao buscar usuário: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualizar perfil do usuário
     * @param int $id ID do usuário
     * @param array $dados Dados para atualização
     * @return bool Sucesso da operação
     */
    public function atualizarPerfil($id, $dados) {
        error_log("[UsuarioModel] Atualizando perfil do usuário ID: " . $id . " com dados: " . print_r($dados, true));
        
        // Preparar dados
        $nome = $this->conn->real_escape_string($dados['nome']);
        $email = $this->conn->real_escape_string($dados['email']);
        $telefone = $this->conn->real_escape_string($dados['telefone'] ?? '');
        $id = $this->conn->real_escape_string($id);

        $query = "UPDATE usuarios 
                  SET nome = '$nome', 
                      email = '$email', 
                      telefone = '$telefone' 
                  WHERE id = '$id'";
        
        error_log("[UsuarioModel] Executando query de atualização: " . $query);
        $resultado = $this->conn->query($query);
        error_log("[UsuarioModel] Resultado da atualização: " . ($resultado ? "Sucesso" : "Falha - " . $this->conn->error));
        
        return $resultado;
    }

    /**
     * Alterar senha do usuário
     * @param int $id ID do usuário
     * @param string $senhaAtual Senha atual
     * @param string $novaSenha Nova senha
     * @return bool Sucesso da operação
     */
    public function alterarSenha($id, $senhaAtual, $novaSenha) {
        error_log("[UsuarioModel] Alterando senha do usuário ID: " . $id);
        
        $id = $this->conn->real_escape_string($id);

        // Primeiro, verificar se a senha atual está correta
        $query = "SELECT senha FROM usuarios WHERE id = '$id'";
        error_log("[UsuarioModel] Verificando senha atual: " . $query);
        
        $result = $this->conn->query($query);

        if ($result && $result->num_rows > 0) {
            $usuario = $result->fetch_assoc();

            // Verificar senha atual
            if (!password_verify($senhaAtual, $usuario['senha'])) {
                error_log("[UsuarioModel] Senha atual incorreta para usuário ID: " . $id);
                return false;
            }

            // Atualizar senha
            $novaSenhaCriptografada = password_hash($novaSenha, PASSWORD_DEFAULT);
            $novaSenhaCriptografada = $this->conn->real_escape_string($novaSenhaCriptografada);

            $query = "UPDATE usuarios SET senha = '$novaSenhaCriptografada' WHERE id = '$id'";
            error_log("[UsuarioModel] Atualizando senha: " . $query);
            
            $resultado = $this->conn->query($query);
            error_log("[UsuarioModel] Resultado da atualização de senha: " . ($resultado ? "Sucesso" : "Falha - " . $this->conn->error));
            
            return $resultado;
        }

        error_log("[UsuarioModel] Usuário não encontrado para alteração de senha, ID: " . $id);
        return false;
    }

    /**
     * Listar usuários com filtros e paginação
     * @param int $pagina Número da página
     * @param int $porPagina Quantidade de itens por página
     * @param string|null $status Filtro de status (ativo/inativo/null para todos)
     * @param string|null $tipo Filtro de tipo de usuário
     * @param string|null $busca Termo de busca (nome, email ou usuário)
     * @return array Lista de usuários filtrados e paginados
     */
    public function listarUsuariosComFiltros($pagina = 1, $porPagina = 10, $status = null, $tipo = null, $busca = null) {
        $usuarios = [];
        $offset = ($pagina - 1) * $porPagina;
        
        // Preparar condições WHERE
        $where = [];
        $params = [];
        $types = "";
        
        // Filtro de status
        if ($status !== null) {
            $where[] = "status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Filtro de tipo
        if ($tipo !== null) {
            $where[] = "tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }
        
        // Filtro de busca
        if ($busca !== null && trim($busca) !== '') {
            $where[] = "(nome LIKE ? OR email LIKE ? OR usuario LIKE ?)";
            $busca = "%{$busca}%";
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
            $types .= "sss";
        }
        
        // Montar cláusula WHERE
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Consulta principal
        $query = "SELECT id, nome, usuario, email, tipo, status, data_cadastro, ultimo_acesso 
                  FROM usuarios 
                  $whereClause 
                  ORDER BY id DESC 
                  LIMIT $offset, $porPagina";
        
        try {
            if (empty($params)) {
                // Sem parâmetros, consulta simples
                $result = $this->conn->query($query);
            } else {
                // Com parâmetros, usar prepared statement
                $stmt = $this->conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
                }
                
                // Vincular parâmetros
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            }
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $usuarios[] = $row;
                }
            }
            
            return $usuarios;
        } catch (Exception $e) {
            error_log("[UsuarioModel] Erro ao listar usuários com filtros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar total de usuários com filtros
     * @param string|null $status Filtro de status (ativo/inativo/null para todos)
     * @param string|null $tipo Filtro de tipo de usuário
     * @param string|null $busca Termo de busca (nome, email ou usuário)
     * @return int Total de usuários que atendem aos filtros
     */
    public function contarUsuariosComFiltros($status = null, $tipo = null, $busca = null) {
        // Preparar condições WHERE
        $where = [];
        $params = [];
        $types = "";
        
        // Filtro de status
        if ($status !== null) {
            $where[] = "status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Filtro de tipo
        if ($tipo !== null) {
            $where[] = "tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }
        
        // Filtro de busca
        if ($busca !== null && trim($busca) !== '') {
            $where[] = "(nome LIKE ? OR email LIKE ? OR usuario LIKE ?)";
            $busca = "%{$busca}%";
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
            $types .= "sss";
        }
        
        // Montar cláusula WHERE
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Consulta de contagem
        $query = "SELECT COUNT(*) as total FROM usuarios $whereClause";
        
        try {
            if (empty($params)) {
                // Sem parâmetros, consulta simples
                $result = $this->conn->query($query);
            } else {
                // Com parâmetros, usar prepared statement
                $stmt = $this->conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Erro ao preparar consulta de contagem: " . $this->conn->error);
                }
                
                // Vincular parâmetros
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            }
            
            if ($result && $row = $result->fetch_assoc()) {
                return (int)$row['total'];
            }
            
            return 0;
        } catch (Exception $e) {
            error_log("[UsuarioModel] Erro ao contar usuários com filtros: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar total de usuários
     * @return int Total de usuários
     */
    public function contarUsuarios() {
        $query = "SELECT COUNT(*) as total FROM usuarios";
        $result = $this->conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total'];
        }
        
        return 0;
    }

    /**
     * Contar usuários por nível de acesso
     * @param int $nivel Nível de acesso (1=Usuário, 2=Operador, 3=Administrador, 4=Super Admin)
     * @return int Total de usuários com o nível especificado
     */
    public function contarUsuariosPorNivel($nivel) {
        // Mapear nível para tipo de usuário
        $tipos = [
            1 => 'operador',  // Apostador
            2 => 'operador',  // Revendedor/Operador
            3 => 'operador',  // Administrador
            4 => 'admin'      // Super Admin
        ];
        $tipo = $tipos[$nivel] ?? 'operador';
        
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = ?";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("s", $tipo);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $row = $result->fetch_assoc()) {
                return (int)$row['total'];
            }
        }
        
        return 0;
    }

    /**
     * Contar usuários ativos
     * @return int Total de usuários ativos
     */
    public function contarUsuariosAtivos() {
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE status = 'ativo'";
        $result = $this->conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total'];
        }
        
        return 0;
    }
}
?> 