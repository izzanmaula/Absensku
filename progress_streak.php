<?php
$query_streak = "SELECT current_streak, max_streak, jumlah_poin 
FROM poin_user WHERE user_id = ?";
$stmt_streak = $conn->prepare($query_streak);
$stmt_streak->bind_param("i", $user_id);
$stmt_streak->execute();
$streak_data = $stmt_streak->get_result()->fetch_assoc();
?>