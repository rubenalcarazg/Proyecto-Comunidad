<?php
require_once 'conexion.php';

function obtenerVotacion($id_votacion) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM votacion WHERE id_votacion = :id");
        $stmt->bindParam(':id', $id_votacion);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener la votación: " . $e->getMessage());
        return null;
    }
}


?>