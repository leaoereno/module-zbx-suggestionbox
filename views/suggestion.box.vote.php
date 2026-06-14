<?php
/**
 * View: suggestion.box.vote.php  (layout.json)
 */
echo json_encode([
    'success'   => $data['success']   ?? false,
    'voted'     => $data['voted']     ?? false,
    'voteCount' => $data['voteCount'] ?? 0,
    'error'     => $data['error']     ?? null,
]);
