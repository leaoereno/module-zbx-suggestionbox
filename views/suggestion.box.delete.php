<?php
/**
 * View: suggestion.box.delete.php  (layout.json)
 */
echo json_encode([
    'success' => $data['success'] ?? false,
    'error'   => $data['error']   ?? null,
]);
