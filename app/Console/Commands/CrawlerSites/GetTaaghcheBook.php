<?php

namespace App\Console\Commands\CrawlerSites;

use App\Models\BookTaaghche;
use App\Models\TaaghcheComment;
use Illuminate\Console\Command;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class GetTaaghcheBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:taaghcheBook {start} {end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl books from the taacghche';

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
     * @return int
     */
    public function handle()
    {
        $start = (int)$this->argument('start');
        $end = (int)$this->argument('end');
        for ($i=$start; $i<=$end; $i++) {
            $client = new HttpBrowser(HttpClient::create(['timeout' => 30]));
            echo " \n ---------- Try Get BOOK " . $i . "              ---------- ";
            echo "\n $i";
            $crawler = $client->request('GET', 'https://taaghche.com/audiobook/' . $i, [
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                ],
            ]);
            $status_code = $client->getInternalResponse()->getStatusCode();

            if ($status_code == 200) {
                // Filter the script tag with id "__NEXT_DATA__"
                $data = $crawler->filter('script#\\__NEXT_DATA__')->first()->text();

                // Decode the JSON content into a PHP array
                $decodedData = json_decode($data, true);


                $insertCommentArray = [];
                $more_content = '';
                $contentForInsert = '';
                isset($decodedData['props']['pageProps']['bookPage']['comments']) ?
                    $comments = $decodedData['props']['pageProps']['bookPage']['comments']:
                    $comments = [];
                $bookInfo = $decodedData['props']['pageProps']['bookPage']['book'];
                foreach ($comments as $comment) {
                    $insertCommentArray[] = [
                        'comment' =>isset($comment['comment'])? html_entity_decode($comment['comment']):'',
                        'name' =>isset($comment['name']) ?html_entity_decode($comment['nickname']) :'',
                        'rate' => $comment['rate'] ?? 0,
                        'taaghche_book_id' => $comment['bookId']
                    ];
                }
                if (isset($decodedData['props']['pageProps']['bookPage']['bookRows'])) {
                    foreach ($decodedData['props']['pageProps']['bookPage']['bookRows'] as $bookRow) {
                        if ($bookRow['type'] = 4) {
                            $htmlContent = html_entity_decode($bookRow['content']);
                            $titles = explode('</h2>', $htmlContent);
                            if (count($titles) == 3){
                                $contentForInsert = strip_tags($titles[1]);
                                break;
                            } elseif (count($titles) == 4) {
                                $more_content = strip_tags(explode('<h2>',$titles[3])[0]);
                                $contentForInsert = strip_tags($titles[1]);
                                break;
                            }
                            else {
                                $contentForInsert = strip_tags($titles[0]);
                                break;
                            }
                        }
                    }
                }
                $partnerArray = [];
                $partnerNames = '';
                $flag =true;

                if (isset($bookInfo)) {
                    foreach ($bookInfo['authors'] as $author) {
                        if ($flag) {
                            $partnerNames .= html_entity_decode($author['firstName']) . ' ' . html_entity_decode($author['lastName']);
                            $flag = false;
                        } else {
                            $partnerNames .= '#' . html_entity_decode($author['firstName']) . ' ' . html_entity_decode($author['lastName']);
                        }
                        $partnerArray [] = [
                            'id' => html_entity_decode($author['id']),
                            'firstName' => html_entity_decode($author['firstName']),
                            'lastName' => html_entity_decode($author['lastName']),
                            'type' => html_entity_decode($author['type']),
                            'slug' => html_entity_decode($author['slug']),
                        ];
                    };
                }
                $book = [
                    'recordNumber' => $bookInfo['id'],
                    'title' => html_entity_decode($bookInfo['title']) ?? '',
                    'nasher' => html_entity_decode($bookInfo['publisher']) ?? '',
                    'saleNashr' => $bookInfo['publishDate'] ?? '',
                    'tedadSafe' => $bookInfo['numberOfPages']?? '',
                    'shabak' => isset($bookInfo['ISBN']) and is_numeric($bookInfo['ISBN'])? $bookInfo['ISBN'] : html_entity_decode($bookInfo['ISBN']),
                    'images' => $bookInfo['coverUri']?? '',
                    'content' => $contentForInsert ,
                    'price' => $bookInfo['price']?? 0,
                    'partnerArray' =>json_encode($partnerArray, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    'fileSize' => isset($bookInfo['filesSize']) ?html_entity_decode($bookInfo['filesSize']): 0 ,
                    'commentsCount' => $decodedData['props']['pageProps']['bookPage']['commentsCount'] ?? 0,
                    'ghatechap' => 'null',
                    'jeld' => 'null',
                    'authorsname' => $partnerNames,
                    'authorbio' => 'null',
                    'authorimg' => 'null',
                    'rating' => isset($bookInfo['rating']) ? round($bookInfo['rating'])  :0,
                    'commentcrawl' => 0,
                    'some_part_of_book' => $more_content
                ];
                BookTaaghche::create($book);
                foreach ($insertCommentArray as $value){
                    TaaghcheComment::create($value);
                }
            }
        }
        return 1;
    }
}
