<?php
// Write to log.
debug_log('quest_create()');

// For debug.
//debug_log($update);
//debug_log($data);

// Pokestop id.
$pokestop_id = $data['id'];

// Check if quest already exists for this pokestop.
$quest_in_db = quest_duplication_check($pokestop_id);

// Init keys.
$keys = [];

// Quest already in database or new
if (!$quest_in_db) {
    // Build message string.
    $msg = '';
    $stop = get_pokestop($pokestop_id, false);
    $msg .= getTranslation('pokestop') . ': <b>' . $stop['pokestop_name'] . '</b>' . (!empty($stop['address']) ? (CR . $stop['address']) : '');
    $msg .= CR . CR . '<b>' . getTranslation('quest_select_type') . '</b>';

    // Create the keys.
    $keys = quest_type_keys($pokestop_id);
} else {
    // Quest already in the database for this pokestop.
    $msg = EMOJI_WARN . '<b> ' . getTranslation('quest_already_submitted') . ' </b>' . EMOJI_WARN . CR . CR;
    $quest = get_quest($quest_in_db['id']);
    $quest_id = $quest_in_db['id'];
    $keys_delete = universal_key($keys, $quest_id, 'quest_delete', '0', getTranslation('delete'));
    $keys_exit = universal_key($keys, '0', 'exit', '0', getTranslation('abort'));
    $msg .= get_formatted_quest($quest);

    // Empty keys.
    $keys = array_merge($keys_delete,$keys_exit);
}

// Edit the message.
edit_message($update, $msg, $keys, ['disable_web_page_preview' => 'true']);

// Build callback message string.
$callback_response = 'OK';

// Answer callback.
answerCallbackQuery($update['callback_query']['id'], $callback_response);

exit();
