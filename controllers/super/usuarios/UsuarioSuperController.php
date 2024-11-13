<?php
class UsuarioSuperController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los usuarios con información de sus balnearios asociados
     */
    public function obtenerUsuarios() {
        $query = "SELECT u.*, b.nombre_balneario,
                 (SELECT COUNT(*) FROM boletines WHERE id_usuario = u.id_usuario) as total_boletines
                 FROM usuarios u
                 LEFT JOIN balnearios b ON u.id_balneario = b.id_balneario
                 ORDER BY u.fecha_registro DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los detalles de un usuario específico
     */
    public function obtenerUsuario($id_usuario) {
        $query = "SELECT u.*, b.nombre_balneario,
                 (SELECT COUNT(*) FROM boletines WHERE id_usuario = u.id_usuario) as total_boletines
                 FROM usuarios u
                 LEFT JOIN balnearios b ON u.id_balneario = b.id_balneario
                 WHERE u.id_usuario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Genera una contraseña aleatoria segura
     */
    private function generarPassword($longitud = 12) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
        $password = '';
        $max = strlen($caracteres) - 1;
        
        for ($i = 0; $i < $longitud; $i++) {
            $password .= $caracteres[random_int(0, $max)];
        }
        
        return $password;
    }

    /**
     * Crea un nuevo usuario
     * @return array con el resultado y la contraseña generada
     */
    public function crearUsuario($datos) {
        try {
            // Verificar si el email ya existe
            $query = "SELECT id_usuario FROM usuarios WHERE email_usuario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $datos['email_usuario']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("El correo electrónico ya está registrado");
            }

            // Generar contraseña
            $password = $this->generarPassword();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $query = "INSERT INTO usuarios (
                        nombre_usuario, email_usuario, password_usuario,
                        rol_usuario, id_balneario
                    ) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "ssssi",
                $datos['nombre_usuario'],
                $datos['email_usuario'],
                $password_hash,
                $datos['rol_usuario'],
                $datos['id_balneario']
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'id_usuario' => $stmt->insert_id,
                    'password' => $password
                ];
            }

            throw new Exception("Error al crear el usuario");

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza los datos de un usuario
     */
    public function actualizarUsuario($id_usuario, $datos) {
        try {
            // Verificar si el email ya existe para otro usuario
            $query = "SELECT id_usuario FROM usuarios 
                     WHERE email_usuario = ? AND id_usuario != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $datos['email_usuario'], $id_usuario);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("El correo electrónico ya está en uso");
            }

            // Actualizar usuario
            $query = "UPDATE usuarios SET 
                        nombre_usuario = ?,
                        email_usuario = ?,
                        rol_usuario = ?,
                        id_balneario = ?
                     WHERE id_usuario = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssii",
                $datos['nombre_usuario'],
                $datos['email_usuario'],
                $datos['rol_usuario'],
                $datos['id_balneario'],
                $id_usuario
            );

            return $stmt->execute();

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Verifica si un usuario puede ser eliminado
     */
    public function puedeEliminarUsuario($id_usuario) {
        // Verificar si tiene boletines asociados
        $query = "SELECT COUNT(*) as total FROM boletines WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['total'] == 0;
    }

    /**
     * Elimina un usuario
     */
    public function eliminarUsuario($id_usuario) {
        try {
            if (!$this->puedeEliminarUsuario($id_usuario)) {
                throw new Exception("No se puede eliminar el usuario porque tiene boletines asociados");
            }

            $query = "DELETE FROM usuarios WHERE id_usuario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_usuario);
            
            return $stmt->execute();

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Genera una nueva contraseña para un usuario
     */
    public function regenerarPassword($id_usuario) {
        try {
            $password = $this->generarPassword();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $query = "UPDATE usuarios SET password_usuario = ? WHERE id_usuario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $password_hash, $id_usuario);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'password' => $password
                ];
            }

            throw new Exception("Error al regenerar la contraseña");

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene la lista de balnearios para el select
     */
    public function obtenerBalnearios() {
        $query = "SELECT id_balneario, nombre_balneario 
                 FROM balnearios 
                 ORDER BY nombre_balneario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?> 