<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'مقدار :attribute باید قابل تایید باشد',
    'active_url'           => 'مقدار :attribute باید یک آدرس معتبر باشد',
    'after'                => 'مقدار :attribute باید پس از :date باشد',
    'after_or_equal'       => 'مقدار :attribute باید برابر یا بیشتر از :date باشد',
    'alpha'                => 'مقدار :attribute تنها باید حروف باشد',
    'alpha_dash'           => 'مقدار :attribute باید تنها حروف،اعداد و dash باشد',
    'alpha_num'            => 'مقدار :attribute باید تنها اعداد و حروف باشد',
    'array'                => 'مقدار :attribute باید آرایه باشد',
    'before'               => 'مقدار :attribute باید قبل از :date باشد',
    'before_or_equal'      => 'مقدار :attribute باید کمتر یا برابر :date باشد',
    'between'              => [
        'numeric' => 'مقدار :attribute باید بین :min و :max باشد',
        'file'    => 'مقدار :attribute باید بین :min kb و :max kb باشد',
        'string'  => 'مقدار :attribute باید از حرف :min بیشتر و  از حرف :max کمتر باشد',
        'array'   => 'مقدار :attribute باید بین :min و :max باشد',
    ],
    'boolean'              => 'مقدار :attribute تنها دوحالت true/false می تواند باشد',
    'confirmed'            => ':attribute همخوانی ندارد',
    'date'                 => ':attribute تاریخ معتبر نیست',
    'date_format'          => ':attribute الگوی :format ندارد',
    'different'            => 'مقدار :attribute و :other متفاوت باید باشد',
    'digits'               => 'مقدار :attribute باید ارقام :digits باشد',
    'digits_between'       => 'ارقام :attribute باید بین :min و :max باشد',
    'dimensions'           => ':attribute اندازه غیرمعتبر دارد',
    'distinct'             => ':attribute تکراری می باشد',
    'email'                => ':attribute نامعتبر است',
    'exists'               => ':attribute انتخاب شده نامعتبر است',
    'file'                 => ':attribute فایل باید باشد',
    'filled'               => ':attribute باید مقدار داشته باشد',
    'image'                => ':attribute باید تصویر باشد',
    'in'                   => ':attribute انتخاب شده نامعتبر است',
    'in_array'             => 'مقدار :attribute در :other موجود نیست',
    'integer'              => ':attribute باید عدد باشد',
    'ip'                   => ':attribute باید آدرس ip معتبر باشد',
    'ipv4'                 => ':attribute باید آدرس IPv4 معتبر باشد',
    'ipv6'                 => ':attribute باید آدرس IPv6 معتبر باشد',
    'json'                 => ':attribute باید متن JSON معتبر باشد',
    'max'                  => [
        'numeric' => ':attribute نباید بیشتر از :max باشد',
        'file'    => ':attribute نباید بیشتر از :max kb باشد',
        'string'  => ':attribute نباید بیشتر از :max حرف باشد',
        'array'   => 'در :attribute نباید بیشتر از :max مقدار باشد',
    ],
    'mimes'                => ':attribute باید نوع :type داشته باشد',
    'mimetypes'            => ':attribute باید نوع :type داشته باشد',
    'min'                  => [
        'numeric' => 'مقدار :attribute باید حداقل :min باشد',
        'file'    => ':attribute باید حداقل :min kb باشد',
        'string'  => ':attribute باید حداقل :min حرف باشد',
        'array'   => ':attribute باید حداقل :min مقدار داشته باشد',
    ],
    'not_in'               => 'مقدار انتخاب شده برای :attribute نامعتبر است',
    'not_regex'            => 'الگوی :attribute نامعتبر است',
    'numeric'              => ':attribute باید عدد باشد',
    'present'              => ':attribute باید مقدار داشته باشد',
    'regex'                => 'الگوی :attribute نامعتبر است',
    'required'             => ':attribute را تکمیل نمایید',
    'required_if'          => ':attribute وقتی که مقدار :value دارد ضروری است',
    'required_unless'      => ':attribute وقتی که :value نیست مورد نیاز است',
    'required_with'        => ':attribute وقتی که :values مقدار دارد مورد نیاز است',
    'required_with_all'    => ':attribute وقتی که :values مقدار دارد مورد نیاز است',
    'required_without'     => ':attribute ، وقتی که :values مقدار ندارد ؛ مورد نیاز است. ',
    'required_without_all' => ':attribute وقتی که هیچ کدام از :values مقدار ندارند مورد نیاز است',
    'same'                 => ':attribute و :values باید همخوانی داشته باشد',
    'size'                 => [
        'numeric' => 'اندازه :attribute باید :size باشد',
        'file'    => 'اندازه :attribute باید :size kb باشد',
        'string'  => 'اندازه :attribute باید :size حرف باشد',
        'array'   => 'اندازه :attribute باید :size مقدار باشد',
    ],
    'string'               => ':attribute باید متنی باشد',
    'timezone'             => ':attribute باید موقعیت زمانی معتبر باشد',
    'unique'               => ':attribute تکراری است',
    'uploaded'             => ':attribute در آپلود موفق نشد',
    'url'                  => ':attribute نامعتبر است',
    'recaptcha' => ':attribute نامعتبر است',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        //language
        'language' => 'زبان',
        'language_name' => 'نام زبان',
        'direction' => 'جهت',
        'align' => 'تراز',
        'opposite_direction' => 'مخالف جهت اصلی',
        'opposite_align' => 'مخالف تراز اصلی',
        'site_title' => 'عنوان اصلی سایت',
        'home_title' => 'عنوان صفحه نخست',
        'home_keyword' => 'کلید واژه صفحه اصلی',
        'home_description' => 'توضیحات صفحه اصلی',
        'home_text' => '',
        'home_text_bottom' => '',
        'flag' => 'پرچم',
        'language_status_id'=>'وضعیت زبان',
        'header_image'=>'لوگوی هدر',
        'footer_image'=>'لوگوی فوتر',
        'favicon_image'=>'آیکون سایت',
        'watermark_image'=>'واترمارک',
        'contact_title'=>'عنوان صفحه',
        'contact_text'=>'متن صفحه',
        'contact_keyword'=>'کلیدواژه صفحه',
        'about_title'=>'عنوان درباره ما',
        'about_text'=>'متن صفحه',
        'about_keyword'=>'کلیدواژه صفحه',
        /// link and linkgroup
        'linkgroup_id'=>"گروه پیوند",
        //news group
        'en_title'=>'عنوان انگلیسی',
        'news_count'=>'تعداد خبر در صفحات غیر آرشیوی',
        'slide'=>'اسلاید',
        'person'=>'شخص',

        //comment
        'comment_status_id'=>'وضعیت نظر',

        //menu
        'url'=>'آدرس پیوند',

        //tag 
        'tag_status_id'=>'وضعیت تگ',

        //Advertisment
        'start_date'=>'تاریخ شروع',
        'end_date'=>'تاریخ پایان',
        'width'=>'عرض - پهنا',
        'height'=>'ارتفاع',
        'advertisment_file'=>'فایل تبلیغ',
        //
        'name' => 'نام',
        'username' => 'نام کاربری',
        'email' => 'پست الکترونیکی',
        'email' => 'پست الکترونیکی',
        'first_name' => 'نام',
        'last_name' => 'نام خانوادگی',
        'family' => 'نام خانوادگی',
        'password' => 'رمز عبور',
        'password_confirmation' => 'تاییدیه ی رمز عبور',
        'city' => 'شهر',
        'country' => 'کشور',
        'address' => 'نشانی',
        'phone' => 'تلفن',
        'mobile' => 'تلفن همراه',
        'age' => 'سن',
        'sex' => 'جنسیت',
        'gender' => 'جنسیت',
        'day' => 'روز',
        'month' => 'ماه',
        'year' => 'سال',
        'hour' => 'ساعت',
        'minute' => 'دقیقه',
        'second' => 'ثانیه',
        'title' => 'عنوان',
        'sort_number'=>'شماره ترتیب',
        'text' => 'متن',
        'content' => 'محتوا',
        'description' => 'توضیحات',
        'excerpt' => 'گلچین کردن',
        'date' => 'تاریخ',
        'time' => 'زمان',
        'available' => 'موجود',
        'size' => 'اندازه',
        'file' => 'فایل',
        'fullname' => 'نام کامل',
        'g-recaptcha-response' => 'کد کپچا',

        ///////
        'parentid' => 'سرگروه',
        'parent_id' => 'سرگروه',
        'newsgroup' => 'عنوان سرویس خبری',
        'titleen' => 'عنوان انگلیسی',
        'type' => 'نوع سرویس خبری',
        'sort' => 'شماره ترتیبی',
        'newsview' => 'تعداد نمایش خبر',
        'newscount' => 'تعداد خبر',
        'languageid' => 'زبان',
        'priority' => 'اولویت',
        'change' => 'زمان تغییر',
        'name' => 'نام',
        'slug' => 'کلید',
        'group_id' => 'گروه',
        'permission_group_id' => 'گروه',
        'sort_id' => 'شماره ترتیبی',
        'color' => 'رنگ',
        'agent' => 'اجنت مرورگر',
        'homekeyword' => 'کلید واژه صفحه اصلی',
        'keyword' => 'کلیدواژه',
        'homedescription' => 'توضیحات صفحه اصلی',

        'commentid' => 'شناسه نظر',
        'email' => 'آدرس پست الکترونیکی',
        'active' => 'وضعیت',
        'news' => 'تیتر خبر',
        'lead' => 'لید خبر',
        'pretitle' => 'روتیتر (تیتر فرعی) خبر',
        'source' => 'منبع',
        'newsgroupid' => 'سرویس خبری',
        'newsgroup_id' => 'سرویس خبری',
        'context' => 'متن',
        'startdate' => 'تاریخ',
        'enddate' => 'تاریخ',
        'starttime' => 'زمان',
        'tune' => 'کوک زمان',
        'on' => 'فعال',
        'order' => 'شماره ترتیب',
        'home title' => 'عنوان صفحه نخست',
        'site_title' => 'عنوان اصلی سایت',
        'home keyword' => 'کلید واژه صفحه اصلی',
        'home description' => 'توضیحات صفحه اصلی',
        "isbn2"=> "شابک 10 رقمی",
        "isbn3"=> "شابک 13 رقمی",
        "coverPrice"=> "قیمت پشت جلد",
        "publisherId"=>"ناشر",
        "newPublisher"=>"ناشر جدید",
        "pageCount"=>"تعداد صفحه",
        "weight"=>"وزن",
        "printNumber"=>"نوبت چاپ",
        "circulation"=>"تیراژ",
        "publishDate"=>"تاریخ انتشار",
    ],

];
