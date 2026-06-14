<?php
/**
 * View: suggestion.box.save.php  (layout.json)
 */
echo json_encode([
    'success'      => $data['success']      ?? false,
    'suggestionid' => $data['suggestionid'] ?? null,
    'error'        => $data['error']        ?? null,
]);
