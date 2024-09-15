<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\MongoDBModels\DioSubject;
use Illuminate\Console\Command;

class MakeDioSubjectsCollectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dio_subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a collection name dio_subjects';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return Bool
     */
    public function handle()
    {
        // TODO : NEW
        //______________________________________lvl 0______________________________________//
        $this->addToDioSubjects(1,"عمومی",'public' ,0 ,1,0,null,false);
        $this->addToDioSubjects(2 , "دانشگاهی" , 'edu', 0 ,1,0,null,false);
        $this->addToDioSubjects(3 , "کودک و نوجوان" , 'child', 0 ,1,0,null,false);

        //______________________________________lvl 1______________________________________//
        $this->addToDioSubjects(101,"ادبیات","public",1,1,1,null,false);
        $this->addToDioSubjects(102,"تاریخ" , 'public' , 1,1,1,null,false);
        $this->addToDioSubjects(103,"روانشناسی",'public',1,1,1,null,false);
        $this->addToDioSubjects(104,"علوم اجتماعی",'public',1,1,1,null,false);
        $this->addToDioSubjects(105,"فلسفه",'public',1,1,1,null,false);
        $this->addToDioSubjects(106,"دین",'public',1,1,1,null,false);
        $this->addToDioSubjects(107,"زبان خارجی",'public',1,1,1,null,false);
        $this->addToDioSubjects(108,"سرگرمی",'public',1,0,1,[
            ['start' =>"793",'end'=>"795.999"],
            ['start'=>'398.6','end'=>'398.6999']
        ],false);
        $this->addToDioSubjects(109,"مهارتهای کاربردی",'public',1,0,1,[
            ['start'=>'670','end'=>'680.999']
        ],false);
        $this->addToDioSubjects(110,"اطلاعات عمومی",'public',1,0,1,[
            ['start'=>'641','end'=>'648.999'],
            ['start'=>'030','end'=>'039.999']
        ],false);
        $this->addToDioSubjects(111,"انقلاب اسلامی",'public',1,1,1,null,false);
        $this->addToDioSubjects(112,"هنر",'public',1,1,1,null,false);


        $this->addToDioSubjects(202,"علوم کاربردی","edu",2,1,1,null,false);
        $this->addToDioSubjects(201,"علوم پایه","edu",2,1,1,null,false);


        $this->addToDioSubjects(301,"شعر","child",3,1,1,[
            ['start'=>'8fa1','end'=>'8fa1.999'],
            ['start'=>'811','end'=>'811.999'],
            ['start'=>'821','end'=>'821.999'],
            ['start'=>'831','end'=>'831.999'],
            ['start'=>'841','end'=>'841.999'],
            ['start'=>'851','end'=>'851.999'],
            ['start'=>'861','end'=>'861.999'],
            ['start'=>'871','end'=>'871.999'],
            ['start'=>'881','end'=>'881.999']
        ],false);
        $this->addToDioSubjects(302,"تاریخ و جفرافیا","child",3,1,1,null,false);
        $this->addToDioSubjects(303,"هنر","child",3,0,1,[
            ['start'=>'700','end'=>'799.999']

        ],true);
        $this->addToDioSubjects(304,"معارف دینی","child",3,0,1,[
            ['start'=>'200','end'=>'299.999']
        ],false);
        $this->addToDioSubjects(305,"مهارت قردی و اجتماعی","child",3,0,1,[
            ['start'=>'150','end'=>'159.999'],
            ['start'=>'170','end'=>'179.999'],
            ['start'=>'300','end'=>'399.999'],
            ['start'=>'640','end'=>'659.999']
        ],true);
        $this->addToDioSubjects(306,"آموزشی","child",3,0,1,[
            ['start'=>'153','end'=>'153.8999'],
            ['start'=>'370','end'=>'372.999'],
            ['start'=>'4fa0','end'=>'4fa9.999']
        ],false);
        $this->addToDioSubjects(307,"پرورش فکر","child",3,0,1,[
            ['start'=>'100','end'=>'149.999'],
            ['start'=>'160','end'=>'169.999'],
            ['start'=>'180','end'=>'199.999']
        ],false);
        $this->addToDioSubjects(308,"زبان خارجی","child",3,0,1,[
            ['start'=>'400','end'=>'499.999']
        ],false);
        $this->addToDioSubjects(309,"دانستنی","child",3,0,1,[
            ['start'=>'000','end'=>'099.999'],
            ['start'=>'333','end'=>'333.999'],
            ['start'=>'320','end'=>'329.999'],
            ['start'=>'338','end'=>'339.999'],
            ['start'=>'360','end'=>'369.999'],
            ['start'=>'380','end'=>'389.999'],
            ['start'=>'500','end'=>'599.999'],
            ['start'=>'600','end'=>'639.999'],
            ['start'=>'660','end'=>'699.999']
        ],false);
        $this->addToDioSubjects(310,"داستان و رمان","child",3,1,1,null,false);
        $this->addToDioSubjects(311,"ورزش و سرگرمی",'child',3,0,1,[
            ['start'=>'153.9','end'=>'153.999'],
            ['start'=>'793','end'=>'799.999']
        ],false);

        //______________________________________lvl 2______________________________________//
        $this->addToDioSubjects(10101,"ادبیات عامه","public",101,0,2,[
            ['start'=>'398.2','end'=>'398.24999'],
            ['start'=>'398.9','end'=>'398.999']
        ],false);
        $this->addToDioSubjects(10102,"داستان فارسی","public",101,0,2,[
            ['start'=>'8fa3','end'=>'8fa3.999'],
            ['start'=>'8fa9.23','end'=>'8fa9.23999'],
            ['start'=>'8fa9.33','end'=>'8fa9.33999'],
            ['start'=>'8fa9.43','end'=>'8fa9.43999'],
            ['start'=>'8fa9.53','end'=>'8fa9.53999'],
            ['start'=>'8fa9.63','end'=>'8fa9.63999'],
            ['start'=>'8fa9.73','end'=>'8fa9.73999'],
            ['start'=>'8fa9.83','end'=>'8fa9.83999'],
            ['start'=>'8fa9.93','end'=>'8fa9.93999'],
            ],false);
        $this->addToDioSubjects(10103,"داستان خارجی","public",101,0,2,[
            ['start'=>'813','end'=>'813.999'],
            ['start'=>'819','end'=>'819.999'],
            ['start'=>'823','end'=>'823.999'],
            ['start'=>'829','end'=>'829.999'],
            ['start'=>'833','end'=>'833.999'],
            ['start'=>'839','end'=>'839.999'],
            ['start'=>'843','end'=>'843.999'],
            ['start'=>'849','end'=>'849.999'],
            ['start'=>'853','end'=>'853.853'],
            ['start'=>'859','end'=>'859.999'],
            ['start'=>'863','end'=>'863.999'],
            ['start'=>'869','end'=>'869.999'],
            ['start'=>'873','end'=>'873.999'],
            ['start'=>'879','end'=>'879.999'],
            ['start'=>'883','end'=>'883.999'],
            ['start'=>'889','end'=>'889.999'],
            ['start'=>'891','end'=>'891.6999'],
            ['start'=>'891.73','end'=>'891.999'],
            ['start'=>'892','end'=>'892.6999'],
            ['start'=>'892.73','end'=>'892.999'],
            ['start'=>'893','end'=>'893.999'],
            ['start'=>'894.353','end'=>'894.35999'],
            ['start'=>'894.3613','end'=>'894.3613999'],
            ['start'=>'894.3643','end'=>'894.3643999'],
            ['start'=>'894.365','end'=>'899.999'],
        ],false);
        $this->addToDioSubjects(10104,"نمایشنامه","public",101,0,2,[
            ['start'=>'894.3642','end'=>'894.3642999'],
            ['start'=>'894.3612','end'=>'894.3612999'],
            ['start'=>'894.352','end'=>'894.352999'],
            ['start'=>'892.72','end'=>'892.72999'],
            ['start'=>'891.72','end'=>'891.72999'],
            ['start'=>'882','end'=>'882.999'],
            ['start'=>'872','end'=>'872.999'],
            ['start'=>'862','end'=>'862.999'],
            ['start'=>'852','end'=>'852.999'],
            ['start'=>'842','end'=>'842.999'],
            ['start'=>'832','end'=>'832.999'],
            ['start'=>'822','end'=>'822.999'],
            ['start'=>'812','end'=>'812.999'],
            ['start'=>'808.82','end'=>'808.82999'],
            ['start'=>'8fa9.92','end'=>'8fa9.92999'],
            ['start'=>'8fa9.82','end'=>'8fa9.82999'],
            ['start'=>'8fa9.72','end'=>'8fa9.72999'],
            ['start'=>'8fa9.62','end'=>'8fa9.62999'],
            ['start'=>'8fa9.52','end'=>'8fa9.52999'],
            ['start'=>'8fa9.42','end'=>'8fa9.42999'],
            ['start'=>'8fa9.32','end'=>'8fa9.32999'],
            ['start'=>'8fa9.22','end'=>'8fa9.22999'],
            ['start'=>'8fa2','end'=>'8fa2.999'],
        ],false);
        $this->addToDioSubjects(10105,"نثر ادبی","public",101,0,2,[
            ['start'=>'080','end'=>'089.999'],
            ['start'=>'8fa5','end'=>'8fa7.999'],
            ['start'=>'8fa9.24','end'=>'8fa9.2999'],
            ['start'=>'8fa9.34','end'=>'8fa9.3999'],
            ['start'=>'8fa9.44','end'=>'8fa9.4999'],
            ['start'=>'8fa9.54','end'=>'8fa9.5999'],
            ['start'=>'8fa9.64','end'=>'8fa9.6999'],
            ['start'=>'8fa9.74','end'=>'8fa9.7999'],
            ['start'=>'8fa9.84','end'=>'8fa9.8999'],
            ['start'=>'8fa9.94','end'=>'8fa9.999'],
            ['start'=>'815','end'=>'817.999'],
            ['start'=>'825','end'=>'827.999'],
            ['start'=>'835','end'=>'837.999'],
            ['start'=>'845','end'=>'847.999'],
            ['start'=>'855','end'=>'857.999'],
            ['start'=>'865','end'=>'867.999'],
            ['start'=>'875','end'=>'877.999'],
            ['start'=>'885','end'=>'887.999'],
            ['start'=>'894.3614','end'=>'894.362'],
            ['start'=>'894.3615','end'=>'894.363999'],
            ['start'=>'894.3645','end'=>'894.364999'],

        ],false);

        $this->addToDioSubjects(10106,"ادبیات پایداری","public",101,1,2,null,false);
        $this->addToDioSubjects(10107,"زبان شناسی","public",101,0,2,[
            ['start'=>'400','end'=>'419.99'],
            ['start'=>'4fa0','end'=>'4fa9.999'],
        ],false);
        $this->addToDioSubjects(10108,"پژوهش و نقد ادبی","public",101,0,2,[
            ['start'=>'8fa9','end'=>'8fa9.0999'],
            ['start'=>'800','end'=>'807.9999'],
            ['start'=>'808.0668','end'=>'808.1999'],
            ['start'=>'808.3','end'=>'808.3999'],
            ['start'=>'808.7','end'=>'808.7999'],
            ['start'=>'808.84','end'=>'808.84999'],
            ['start'=>'809','end'=>'809.999'],
            ['start'=>'810','end'=>'810.999'],
            ['start'=>'814','end'=>'814.999'],
            ['start'=>'818','end'=>'818.999'],
            ['start'=>'820','end'=>'820.999'],
            ['start'=>'824','end'=>'824.999'],
            ['start'=>'828','end'=>'828.999'],
            ['start'=>'830','end'=>'830.999'],
            ['start'=>'834','end'=>'834.999'],
            ['start'=>'834','end'=>'834.999'],
            ['start'=>'840','end'=>'840.999'],
            ['start'=>'844','end'=>'844.999'],
            ['start'=>'848','end'=>'848.999'],
            ['start'=>'850','end'=>'850.999'],
            ['start'=>'854','end'=>'854.999'],
            ['start'=>'858','end'=>'858.999'],
            ['start'=>'860','end'=>'860.999'],
            ['start'=>'864','end'=>'864.999'],
            ['start'=>'868','end'=>'868.999'],
            ['start'=>'870','end'=>'870.999'],
            ['start'=>'874','end'=>'874.999'],
            ['start'=>'878','end'=>'878.999'],
            ['start'=>'880','end'=>'880.999'],
            ['start'=>'884','end'=>'884.999'],
            ['start'=>'888','end'=>'888.999'],
            ['start'=>'890','end'=>'890.999'],
            ['start'=>'891.7','end'=>'891.70999'],
            ['start'=>'892.7','end'=>'892.70999'],
            ['start'=>'894','end'=>'894.350999'],
            ['start'=>'894.36','end'=>'894.360999'],
            ['start'=>'894.3614','end'=>'894.3614999'],
            ['start'=>'894.3644','end'=>'894.3644999']
        ],false);
        $this->addToDioSubjects(10109,"آیین سخنوری","public",101,0,2,[
            ['start'=>'808.5','end'=>'808.5999'],
        ],false);
        $this->addToDioSubjects(10110,"آیین نگارش","public",101,0,2,[
            ['start'=>'808','end'=>'808.0666999'],
            ['start'=>'808.4','end'=>'808.4999'],
            ['start'=>'808.6','end'=>'808.6999'],
        ],true);
        $this->addToDioSubjects(10111,"کلمات قصار و داستان کوتاه","public",101,0,2,[
            ['start'=>'808.8','end'=>'808.80999'],
            ['start'=>'808.83','end'=>'808.83999'],
            ['start'=>'808.85','end'=>'808.85999'],
            ['start'=>'808.86','end'=>'808.999'],
        ],false);
        $this->addToDioSubjects(10112,"َشعر","public",101,1,2,null,false);
        $this->addToDioSubjects(10201,"َتاریخ ایران باستان","public",102,0,2,[
            ['start'=>'291.13','end'=>'291.13999'],
            ['start'=>'955.01','end'=>'955.03999']
        ],false);
        $this->addToDioSubjects(10202,"َتاریخ ایران معاصر","public",102,0,2,[
            ['start'=>'955.074','end'=>'955.082999'],
        ],false);
        $this->addToDioSubjects(10203,"تاریخ ایران پس از اسلام","public",102,0,2,[
            ['start'=>'955.04','end'=>'955.073999'],
        ],false);
        $this->addToDioSubjects(10204,"تاریخ انقلاب اسلامی","public",102,0,2,[
            ['start'=>'955.083','end'=>'955.0841999'],
        ],false);
        $this->addToDioSubjects(10205,"تاریخ جهان","public",102,0,2,[
            ['start'=>'900','end'=>'909.999'],
            ['start'=>'930','end'=>'999.999'],
        ],true);
        $this->addToDioSubjects(10206,"تاریخ محلی","public",102,0,2,[
            ['start'=>'955.1','end'=>'955.999'],
        ],false);
        $this->addToDioSubjects(10207,"کلیات","public",102,0,2,[
            ['start'=>'090','end'=>'099.999'],
            ['start'=>'920','end'=>'929.999'],
            ['start'=>'955','end'=>'955.00999'],
        ],false);
        $this->addToDioSubjects(10208,"جغرافیا","public",102,0,2,[
            ['start'=>'910','end'=>'919.999'],
        ],false);
        $this->addToDioSubjects(10301,"نظریه های روانشناسی","public",103,0,2,[
            ['start'=>'150','end'=>'152.3999'],
            ['start'=>'153','end'=>'153.5999'],
            ['start'=>'154','end'=>'154.999'],
            ['start'=>'155','end'=>'155.2999'],
            ['start'=>'155.3','end'=>'155.3999'],
            ['start'=>'155.6','end'=>'155.999'],
            ['start'=>'158.3','end'=>'158.3999'],
        ],true);
        $this->addToDioSubjects(10302,"اختلالات","public",103,0,2,[
            ['start'=>'152.44','end'=>'152.5999'],
            ['start'=>'159','end'=>'159.999'],
            ['start'=>'177.3','end'=>'177.3999'],
            ['start'=>'362.2','end'=>'362.999'],
            ['start'=>'616.85','end'=>'616.8607'],
            ['start'=>'616.89','end'=>'616.8999'],
            ['start'=>'618.928','end'=>'618.92999'],
        ],false);
        $this->addToDioSubjects(10303,"ازدواج","public",103,0,2,[
            ['start'=>'156','end'=>'157.999'],
            ['start'=>'646.77','end'=>'646.7999'],
            ['start'=>'650','end'=>'650.1999'],
        ],false);
        $this->addToDioSubjects(10304,"خانواده","public",103,0,2,[
            ['start'=>'297.64','end'=>'297.64999'],
            ['start'=>'306.7','end'=>'306.8999'],
        ],false);
        $this->addToDioSubjects(10305,"موفقیت","public",103,0,2,[
            ['start'=>'130','end'=>'139.999'],
            ['start'=>'152.41','end'=>'152.43999'],
            ['start'=>'153.6','end'=>'153.999'],
            ['start'=>'158','end'=>'158.999'],
            ['start'=>'177.4','end'=>'179.999'],
            ['start'=>'248','end'=>'248.999'],
            ['start'=>'291.44','end'=>'291.44999'],
            ['start'=>'332.024','end'=>'332.02999'],
            ['start'=>'395','end'=>'395.999'],
            ['start'=>'640','end'=>'640.999'],
            ['start'=>'646.76','end'=>'646.76999'],
        ],true);
        $this->addToDioSubjects(10306,"تربیت","public",103,0,2,[
            ['start'=>'152.4','end'=>'152.40999'],
            ['start'=>'155.04','end'=>'155.04999'],
            ['start'=>'155.4','end'=>'155.5999'],
            ['start'=>'173','end'=>'173.999'],
            ['start'=>'305.231','end'=>'305.23999'],
            ['start'=>'307.015','end'=>'307.015'],
            ['start'=>'649','end'=>'649.999'],
            ['start'=>'790','end'=>'790.999'],
        ],false);
        $this->addToDioSubjects(10401,"علوم سیاسی","public",104,0,2,[
            ['start'=>'172','end'=>'172.999'],
            ['start'=>'320','end'=>'329.999'],
            ['start'=>'355','end'=>'359.999'],
            ['start'=>'366','end'=>'367.999'],
            ['start'=>'303.3','end'=>'303.999'],
        ],true);
        $this->addToDioSubjects(10402,"جامعه شناسی","public",104,0,2,[
            ['start'=>'174.9','end'=>'174.999'],
            ['start'=>'176','end'=>'176.999'],
            ['start'=>'177','end'=>'177.2999'],
            ['start'=>'300','end'=>'319.999'],
            ['start'=>'320.8','end'=>'320.8999'],
            ['start'=>'360','end'=>'363.999'],
            ['start'=>'955.0044','end'=>'955.0044999'],
        ],true);
        $this->addToDioSubjects(10403,"حقوق","public",104,0,2,[
            ['start'=>'174.3','end'=>'174.3999'],
            ['start'=>'340','end'=>'349.999'],
            ['start'=>'364','end'=>'365.999'],
        ],false);
        $this->addToDioSubjects(10405,"مدیریت","public",104,0,2,[
            ['start'=>'174','end'=>'174.1999'],
            ['start'=>'174.4','end'=>'174.8999'],
            ['start'=>'302.35','end'=>'302.36'],
            ['start'=>'350','end'=>'354.999'],
            ['start'=>'368','end'=>'369.999'],
            ['start'=>'382','end'=>'383.999'],
            ['start'=>'650.2','end'=>'659.999'],
        ],true);
        $this->addToDioSubjects(10406,"بازرگانی و حمل‌ونقل","public",104,0,2,[
            ['start'=>'380','end'=>'389.999'],
        ],true);
        $this->addToDioSubjects(10407,"رسانه و ارتباطات","public",104,0,2,[
            ['start'=>'070','end'=>'079.999'],
            ['start'=>'302.2','end'=>'302.2999'],
            ['start'=>'303','end'=>'303.2999'],
            ['start'=>'384','end'=>'384.999'],
        ],true);
        $this->addToDioSubjects(10408,"مردم‌شناسی","public",104,0,2,[
            ['start'=>'390','end'=>'394.999'],
            ['start'=>'398','end'=>'399.999'],
        ],true);
        $this->addToDioSubjects(10409,"آموزش و پرورش","public",104,0,2,[
            ['start'=>'370','end'=>'379.999'],
            ['start'=>'507','end'=>'507.999'],
        ],false);
        $this->addToDioSubjects(10501,"تاریخ فلسفه","public",105,0,2,[
            ['start'=>'109','end'=>'109.999'],
        ],false);
        $this->addToDioSubjects(10502,"فلسفه محض","public",105,0,2,[
            ['start'=>'100','end'=>'108.999'],
            ['start'=>'110','end'=>'111.00999'],
            ['start'=>'160','end'=>'169.999'],
        ],false);
        $this->addToDioSubjects(10503,"فلسفه مضاف","public",105,0,2,[
            ['start'=>'111.01','end'=>'129.999'],
            ['start'=>'140','end'=>'149.999'],
            ['start'=>'170','end'=>'171.999'],
            ['start'=>'500','end'=>'501.999'],
            ['start'=>'509','end'=>'509.999'],
            ['start'=>'900','end'=>'901.999'],
        ],false);
        $this->addToDioSubjects(10504,"فلسفه غرب","public",105,0,2,[
            ['start'=>'180','end'=>'189.0999'],
            ['start'=>'190','end'=>'199.999'],
        ],false);
        $this->addToDioSubjects(10505,"فلسفه اسلامی","public",105,0,2,[
            ['start'=>'189.1','end'=>'189.999'],
        ],false);
        $this->addToDioSubjects(10601,"کلام اسلامی","public",106,0,2,[
            ['start'=>'297.4','end'=>'297.47999'],
            ['start'=>'297.49','end'=>'297.4999'],
        ],true);
        $this->addToDioSubjects(10602,"اندیشه اسلامی","public",106,0,2,[
            ['start'=>'297','end'=>'297.0999'],
        ],false);
        $this->addToDioSubjects(10603,"اخلاق","public",106,0,2,[
            ['start'=>'297.6','end'=>'297.6999'],
        ],false);
        $this->addToDioSubjects(10604,"عرفان","public",106,0,2,[
            ['start'=>'297.8','end'=>'297.8999'],
        ],false);
        $this->addToDioSubjects(10605,"اصول، فقه و احکام","public",106,0,2,[
            ['start'=>'297.3','end'=>'297.3999'],
        ],false);
        $this->addToDioSubjects(10606,"علوم قرآن","public",106,0,2,[
            ['start'=>'297.1','end'=>'297.1999'],
        ],false);
        $this->addToDioSubjects(10607,"علوم حدیث","public",106,0,2,[
            ['start'=>'297.2','end'=>'297.2999'],
        ],false);
        $this->addToDioSubjects(10608,"ادعیه","public",106,0,2,[
            ['start'=>'297.77','end'=>'297.7999'],
        ],false);
        $this->addToDioSubjects(10609,"آداب و رسوم اسلامی","public",106,0,2,[
            ['start'=>'297.7','end'=>'297.76999'],
        ],false);
        $this->addToDioSubjects(10610,"تاریخ اسلام","public",106,0,2,[
            ['start'=>'297.9','end'=>'297.92999'],
            ['start'=>'297.5','end'=>'297.5999'],
        ],false);
        $this->addToDioSubjects(10611,"اسلام و علوم","public",106,0,2,[
            ['start'=>'297.48','end'=>'297.48999'],
        ],false);
        $this->addToDioSubjects(10612,"ادیان، مذاهب و فرقه‌ها","public",106,0,2,[
            ['start'=>'133.42','end'=>'133.43999'],
            ['start'=>'200','end'=>'296.999'],
            ['start'=>'298','end'=>'299.999'],
        ],true);
        $this->addToDioSubjects(10613,"سیره اهل بیت علیهم السلام","public",106,0,2,[
            ['start'=>'297.46','end'=>'297.462999'],
            ['start'=>'297.93','end'=>'297.93999'],
            ['start'=>'297.95','end'=>'297.96999'],
            ['start'=>'297.973','end'=>'297.974999'],
        ],false);
        $this->addToDioSubjects(10614,"صحابه و بزرگان اسلام","public",106,0,2,[
            ['start'=>'297.94','end'=>'297.94999'],
            ['start'=>'297.97','end'=>'297.999'],
        ],true);
        $this->addToDioSubjects(10701,"آموزشی","public",107,0,2,[
            ['start'=>'420','end'=>'499.999'],
        ],true);
        $this->addToDioSubjects(10702,"دستور زبان","public",107,0,2,[
            ['start'=>'425','end'=>'425.999'],
            ['start'=>'435','end'=>'435.999'],
            ['start'=>'445','end'=>'445.999'],
            ['start'=>'455','end'=>'455.999'],
            ['start'=>'465','end'=>'465.999'],
            ['start'=>'475','end'=>'475.999'],
            ['start'=>'485','end'=>'485.999'],
            ['start'=>'495','end'=>'495.999'],
        ],false);
        $this->addToDioSubjects(10703,"واژه‌نامه","public",107,0,2,[
            ['start'=>'423','end'=>'423.999'],
            ['start'=>'433','end'=>'433.999'],
            ['start'=>'443','end'=>'443.999'],
            ['start'=>'453','end'=>'453.999'],
            ['start'=>'463','end'=>'463.999'],
            ['start'=>'473','end'=>'473.999'],
            ['start'=>'483','end'=>'483.999'],
            ['start'=>'493','end'=>'493.999'],
        ],false);
        $this->addToDioSubjects(11101,"امام خمینی (ره)","public",111,0,2,[
            ['start'=>'955.0842','end'=>'955.0842999'],
        ],false);
        $this->addToDioSubjects(11102,"رهبر معظم انقلاب","public",111,0,2,[
            ['start'=>'955.0844','end'=>'955.0844091999'],
        ],false);
        $this->addToDioSubjects(11103,"مقاومت","public",111,0,2,[
            ['start'=>'956.9','end'=>'956.999'],
        ],false);
        $this->addToDioSubjects(11201,"تاریخ و فلسفه هنر","public",112,0,2,[
            ['start'=>'700','end'=>'709.999'],
        ],false);
        $this->addToDioSubjects(11202,"موسیقی","public",112,0,2,[
            ['start'=>'780','end'=>'789.999'],
        ],false);
        $this->addToDioSubjects(11203,"سینما","public",112,0,2,[
            ['start'=>'791','end'=>'791.999'],
            ['start'=>'808.0667','end'=>'808.0667999'],
            ['start'=>'808.2','end'=>'808.2999'],
        ],false);
        $this->addToDioSubjects(11204,"تئاتر","public",112,0,2,[
            ['start'=>'792','end'=>'792.999'],
        ],false);
        $this->addToDioSubjects(11205,"گرافیک","public",112,0,2,[
            ['start'=>'760','end'=>'769.999'],
        ],false);
        $this->addToDioSubjects(11206,"خوشنویسی","public",112,0,2,[
            ['start'=>'745.619','end'=>'745.619'],
        ],false);
        $this->addToDioSubjects(11207,"نقاشی","public",112,0,2,[
            ['start'=>'750','end'=>'759.999'],
            ['start'=>'740','end'=>'749.999'],
        ],true);
        $this->addToDioSubjects(11208,"معماری و طراحی شهری","public",112,0,2,[
            ['start'=>'710','end'=>'729.999'],
        ],false);
        $this->addToDioSubjects(11209,"هنرهای تجسمی","public",112,0,2,[
            ['start'=>'730','end'=>'739.999'],
        ],false);
        $this->addToDioSubjects(11210,"عکاسی","public",112,0,2,[
            ['start'=>'770','end'=>'779.999'],
        ],false);


        $this->addToDioSubjects(20101,"ریاضی","edu",201,0,2,[
            ['start'=>'510','end'=>'519.999'],
        ],false);
        $this->addToDioSubjects(20102,"شیمی","edu",201,0,2,[
            ['start'=>'540','end'=>'549.999'],
        ],false);
        $this->addToDioSubjects(20103,"نجوم","edu",201,0,2,[
            ['start'=>'520','end'=>'529.999'],
        ],false);
        $this->addToDioSubjects(20104,"فیزیک","edu",201,0,2,[
            ['start'=>'502','end'=>'506.999'],
            ['start'=>'530','end'=>'539.999'],
        ],false);
        $this->addToDioSubjects(20105,"زمین شناسی","edu",201,0,2,[
            ['start'=>'550','end'=>'569.999'],
        ],false);
        $this->addToDioSubjects(20106,"علوم زیستی","edu",201,0,2,[
            ['start'=>'570','end'=>'579.999'],
            ['start'=>'508','end'=>'508.999'],
        ],false);
        $this->addToDioSubjects(20107,"علوم کامپیوتر","edu",201,0,2,[
            ['start'=>'000','end'=>'000.999'],
            ['start'=>'003','end'=>'006.999'],
        ],false);
        $this->addToDioSubjects(20108,"علوم گیاهی","edu",201,0,2,[
            ['start'=>'580','end'=>'589.999'],
        ],false);
        $this->addToDioSubjects(20109,"ریاضی","edu",201,0,2,[
            ['start'=>'590','end'=>'599.999'],
        ],false);
        $this->addToDioSubjects(20201,"پزشکی","edu",202,1,2,null,false);
        $this->addToDioSubjects(20202,"تربیت بدنی","edu",202,0,2,[
            ['start'=>'175','end'=>'175.999'],
            ['start'=>'796','end'=>'799.999'],
        ],false);
        $this->addToDioSubjects(20203,"حسابداری","edu",202,0,2,[
            ['start'=>'657','end'=>'657.999'],
        ],false);
        $this->addToDioSubjects(20204,"دانش و روش تحقیق","edu",202,0,2,[
            ['start'=>'001','end'=>'001.999'],
        ],false);
        $this->addToDioSubjects(20205,"کتابداری و کتابشناسی","edu",202,0,2,[
            ['start'=>'002','end'=>'002.999'],
            ['start'=>'010','end'=>'029.999'],
            ['start'=>'040','end'=>'069.999'],
        ],false);
        $this->addToDioSubjects(20206,"کشاورزی و دامپروری","edu",202,0,2,[
            ['start'=>'630','end'=>'639.999'],
        ],false);
        $this->addToDioSubjects(20207,"علوم مهندسی","edu",202,0,2,[
            ['start'=>'600','end'=>'609.999'],
            ['start'=>'620','end'=>'629.999'],
            ['start'=>'660','end'=>'669.999'],
            ['start'=>'690','end'=>'699.999'],
        ],false);


        $this->addToDioSubjects(31001,"داستان و رمان فارسی","child",310,0,2,[
            ['start'=>'8fa0','end'=>'8fa9.999'],
            ['start'=>'398','end'=>'398.999'],
            ['start'=>'800','end'=>'899.999'],
        ],true);
        $this->addToDioSubjects(31002,"داستان و رمان خارجی","child",310,0,2,[
            ['start'=>'398','end'=>'398.999'],
            ['start'=>'800','end'=>'899.999'],
        ],true);
        $this->addToDioSubjects(31003,"داستان‌های تصویری","child",310,0,2,[
            ['start'=>'741.5','end'=>'741.5999'],
        ],false);
        $this->addToDioSubjects(30201,"تاریخ جهان","child",302,0,2,[
            ['start'=>'900','end'=>'999.999'],
        ],false);
        $this->addToDioSubjects(30202,"تاریخ ایران","child",302,0,2,[
            ['start'=>'955','end'=>'955.999'],
        ],false);


        //        ['start'=>'','end'=>'']
        //______________________________________lvl 3______________________________________//
        $this->addToDioSubjects(1010601,"دفاع مقدس","public",10106,0,3,[
            ['start'=>'955.0843','end'=>'955.084399'],
        ],false);
        $this->addToDioSubjects(1010602,"مدافعان حرم","public",10106,0,3,[
            ['start'=>'955.0844092','end'=>'955.0999'],
        ],false);
        $this->addToDioSubjects(1011201,"شعر کهن فارسی","public",10112,0,3,[
            ['start'=>'8fa1.1','end'=>'8fa1.5999'],
        ],false);
        $this->addToDioSubjects(1011202,"شعر آیینی","public",10112,0,3,[
            ['start'=>'8fa1.05','end'=>'8fa1.05999'],
        ],false);
        $this->addToDioSubjects(1011203,"شعر معاصر فارسی","public",10112,0,3,[
            ['start'=>'8fa1.6','end'=>'8fa1.6999'],
            ['start'=>'8fa9.1','end'=>'8fa9.21999'],
            ['start'=>'8fa9.31','end'=>'8fa9.31999'],
            ['start'=>'8fa9.41','end'=>'8fa9.41999'],
            ['start'=>'8fa9.51','end'=>'8fa9.51999'],
            ['start'=>'8fa9.61','end'=>'8fa9.61999'],
            ['start'=>'8fa9.71','end'=>'8fa9.71999'],
            ['start'=>'8fa9.81','end'=>'8fa9.81999'],
            ['start'=>'8fa9.91','end'=>'8fa9.91999'],
        ],false);
        $this->addToDioSubjects(1011204,"شعر خارجی","public",10112,0,3,[
            ['start'=>'808.81','end'=>'808.81999'],
            ['start'=>'811','end'=>'811.999'],
            ['start'=>'821','end'=>'821.999'],
            ['start'=>'831','end'=>'831.999'],
            ['start'=>'841','end'=>'841.999'],
            ['start'=>'851','end'=>'851.999'],
            ['start'=>'861','end'=>'861.999'],
            ['start'=>'871','end'=>'871.999'],
            ['start'=>'881','end'=>'881.999'],
            ['start'=>'891.71','end'=>'891.71999'],
            ['start'=>'892.71','end'=>'892.71999'],
            ['start'=>'894.351','end'=>'894.3510999'],
            ['start'=>'894.361','end'=>'894.3611999'],
            ['start'=>'894.364','end'=>'894.3641999'],
        ],false);


        $this->addToDioSubjects(2020101,"مبانی پزشکی","edu",20201,0,3,[
            ['start'=>'610','end'=>'610.999'],
            ['start'=>'174.2','end'=>'174.2999'],
        ],false);
        $this->addToDioSubjects(2020106,"جراحی","edu",20201,0,3,[
            ['start'=>'617','end'=>'617.999'],
        ],false);
        $this->addToDioSubjects(2020102,"داروشناسی و درمان","edu",20201,0,3,[
            ['start'=>'615','end'=>'616.999'],
        ],true);
        $this->addToDioSubjects(2020103,"بهداشت","edu",20201,0,3,[
            ['start'=>'613','end'=>'614.999'],
        ],false);
        $this->addToDioSubjects(2020104,"فیزیولوژی","edu",20201,0,3,[
            ['start'=>'611','end'=>'612.999'],
        ],false);
        $this->addToDioSubjects(2020105,"زنان و مامایی","edu",20201,0,3,[
        ['start'=>'618','end'=>'619.999'],
    ],true);

        return true;
    }

    private function addToDioSubjects($id , $title , $dioType,$parentId,$hasChild,$level,$range,$except)
    {
        DioSubject::create([
            'id_by_law' => $id,
            'title' => $title,
            'dio_type' => $dioType,
            'parent_id' => $parentId,
            'has_child' => $hasChild,
            'level' => $level,
            'range' => $range,
            'except' => $except
        ]);
    }
}
