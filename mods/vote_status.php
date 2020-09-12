<?php
// Write to log.
debug_log('vote_status()');

// For debug.
//debug_log($update);
//debug_log($data);

// Check if the user has voted for this raid before.
$rs = my_query(
    "
    SELECT    user_id
    FROM      attendance
      WHERE   raid_id = {$data['id']}
        AND   user_id = {$update['callback_query']['from']['id']}
    "
);

// Get the answer.
$answer = $rs->fetch();

// Write to log.
debug_log($answer);

// Make sure user has voted before.
if (!empty($answer)) {
    // Get status to update
    $status = $data['arg'];
    alarm($data['id'],$update['callback_query']['from']['id'],'status',$status);
    // Update attendance.
    if($status == 'alarm') {
        // Enable / Disable alarm 
        my_query(
        "
        UPDATE attendance
        SET    alarm = CASE
               WHEN alarm = '0' THEN '1'
               ELSE '0'
               END
        WHERE  raid_id = {$data['id']}
        AND    user_id = {$update['callback_query']['from']['id']}
        "
        );

    // Inform User about change
    sendAlertOnOffNotice($data, $update);
    
    } else {
        // All other status-updates are using the short query
        my_query(
	"
        UPDATE  attendance
        SET     arrived = 0,
                raid_done = 0,
                cancel = 0,
                late = 0,
                $status = 1
        WHERE   raid_id = {$data['id']}
        AND     user_id = {$update['callback_query']['from']['id']}
        "
        );
    }

   // Send vote response.
   if($config->RAID_PICTURE) {
       send_response_vote($update, $data,false,false);
    } else {
       send_response_vote($update, $data);
    }
} else {
    // Send vote time first.
    send_vote_time_first($update);
}

exit();
