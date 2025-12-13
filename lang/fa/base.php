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
        "invalid_reaction_source" => "منبع واکنش نامعتبر است. لطفاً یک منبع معتبر مانند کاربر یا دستگاه را مشخص کنید.",
    ],

    'events' => [
        'reaction_added' => [
            'group' => 'واکنش',
            'title' => 'افزودن واکنش',
            'description' => 'هنگامی که یک واکنش اضافه می‌شود، این رویداد فعال می‌شود.',
        ],

        'reaction_removed' => [
            'group' => 'واکنش',
            'title' => 'حذف واکنش',
            'description' => 'هنگامی که یک واکنش حذف می‌شود، این رویداد فعال می‌شود.',
        ],

        'reaction_removing' => [
            'group' => 'واکنش',
            'title' => 'در حال حذف واکنش',
            'description' => 'هنگامی که یک واکنش در حال حذف است، این رویداد فعال می‌شود.',
        ],
    ],

];
