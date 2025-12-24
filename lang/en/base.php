<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Reaction Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Reaction for
    | various messages that we need to display to the user.
    |
    */

    "exceptions" => [
        "invalid_reaction_source" => "Reaction must be associated with either an authenticated user (liker) or a device ID.",
    ],

    "entity_names" => [
        "reaction" => "Reaction",
    ],

    'events' => [
        'reaction_added' => [
            'title' => 'Reaction Added',
            'description' => 'This event is triggered when a reaction is added.',
        ],

        'reaction_removed' => [
            'title' => 'Reaction Removed',
            'description' => 'This event is triggered when a reaction is removed.',
        ],

        'reaction_removing' => [
            'title' => 'Reaction Removing',
            'description' => 'This event is triggered when a reaction is being removed.',
        ],
    ],

];
