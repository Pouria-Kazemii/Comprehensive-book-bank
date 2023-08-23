<?php


return [

    // required
    //'required' => 'نیاز است',
    'required'             => ':attribute را تکمیل نمایید',
    'required_without_all' => 'حداقل یک بخش مورد نیاز است',

    // types
    'integer' => 'باید عدد باشد',
    'string' => 'باید نوشته باشد',
    'bool' => 'باید درست یا غلط باشد',
    'array' => 'باید از نوع ارایه باشد',
    'file' => 'باید از نوع فایل باشد',

    // date time formats
    'date_format' => '۱۴۰۰/۱۰/۰۵ : داده مدل نادرستی دارد . مثال',
    'time_format' => 'داده باید مدل سانیه:دقیقه:ساعت داشته باشد . مثال : ۱۴:۴۵:۰۵',
    'hour_minute_format' => 'داده باید مدل دقیقه:ساعت داشته باشد . مثال : ۲۱:۴۵',
    'minute_second_format' => 'داده باید مدل سانیه:دقیقه داشته باشد . مثال : ۰۰:۴۵',

    // date time before or after
    'date_before_now' => 'باید قبل از تاریخ حال باشد',
    'date_after_now' => 'باید بعد از تاریخ حال باشد',
    'date_before_equal_month' => ' حداکثر میتواند یک ماه با تاریخ حال اخلاف داشته باشد',
    'date_after_equal_start' => 'باید بعد از یا مساوی تاریخ شروع باشد',
    'date_before_equal_year' => 'باید حداکثر یک سال با تاریخ حال اخلاف داشته باشد',
    'end_time_after_start_time' => 'باید بعد از زمان شروع باشد',
    'start_time_before_end_time' => 'باید قبل از زمان پایان باشد',

    // exists
    'exists' => 'یافت نشد',
    'answer_exists' => 'جواب مورد نظر یافت نشد',
    'book_exists' => 'کتاب مورد نظر یافت نشد',
    'comment_exists' => 'کامنت مورد نظر یافت نشد',
    'event_exists' => 'رویداد مورد نظر یافت نشد',
    'question_exists' => 'سوال مورد نظر یافت نشد',

    // unique
    'unique' => 'این مقدار قبلا گرفته شده است',
    'unique_comment' => 'شما قبلا برای این رویداد کامنت ثبت کرده اید',
    'unique_user_event' => 'شما قبلا وارد این رویداد شده اید',
    'unique_question_order' => 'این مقدار برای سوال انتخاب شده از قبل گرفته شده است',
    'unique_answer_order' => 'این مقدار برای جواب انتخاب شده از قبل گرفته شده است',

    // general
    'min' => 'کوتاه است',
    'max' => 'بلند است',
    'gte' => 'باید مقدار مساوی یا بیشتر باشد',
    'gt' => 'باید مقدار بیشتر باشد',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],


    'attributes' => [
        'file'=>'فایل',
        'File'=>'فایل',
    ],
];
