<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Http\Request;

class BookCheckController extends Controller
{
    ///////////////////////////////////////////////General///////////////////////////////////////////////////
    //TODO :first should build bookDossier collection
//    public function initCheck($book)
//    {
//        $id = $book->_id;
//        $isbn = $book->xisbn;
//        $isbn2 = $book->xisbn2;
//        $isbn3 = $book->xisbn3;
//        $name = $book->xname;
//        $publisherIds = null;
//        $where = [];
//        $whereCreator = [];
//
//        //
//        $bookBiPublishers = BookIrBook2::where('_id', $id)->get();
//        if($bookBiPublishers != null)
//        {
//            foreach ($bookBiPublishers as $bookBiPublisher) {
//                foreach ($bookBiPublisher->publisher as $key => $value) {
//                    $publisherId = $value['xpublisher_id'];
//
//                    $where [] = ['xname', $name, $key > 0 ? 'orWhere' : 'where'];
//                    $where [] = ['publisher.xpublisher_id', $publisherId, 'where'];
//                }
//            }
//        }
//        $booksCreators = BookIrBook2::where('_id', $id)->where('creators.xrole', 'نویسنده')->get();
//        if($booksCreators != null)
//        {
//            foreach ($booksCreators as $bookCreators) {
//                foreach ($bookCreators->creators as $key =>$bookCreator) {
//                    $creatorId = $bookCreator['xcreator_id'];
//                    $whereCreator []= ['xname' , $name , $key > 0 ?'orWhere':'where'];
//                    $whereCreator []= ['creators.xcreator_id' , $creatorId , 'where'];
//                }
//            }
//        }
//        $similarBooks = BookIrBook2::where(function ($query) use ($id , $isbn , $isbn2 ,$isbn3 ,$where){
//            $query->where('_id','!=' ,$id)->where('xparent',0)
//                ->where('xrequest_manage_parent','!=' ,1)
//                ->where('xisbn',$isbn)->orWhere('xisbn2',$isbn2)->orWhere('xisbn3',$isbn3);
//                for ($i=0 ; $i<count($where); $i++){
//                    if ($where[$i][2] == 'where'){
//                        $query->where($where[$i][0] , $where[$i][1]);
//                    }
//                    if ($where[$i][2] == 'orWhere'){
//                        $query->orWhere($where[$i][0],$where[$i][1]);
//                    }
//                }
//        })->get();
//
//        if($similarBooks != null)
//        {
//            foreach ($similarBooks as $similarBook)
//            {
//                BookIrBook2::where('_id', $similarBook->_id)->update(['xparent' => $id]);
//            }
//        }
//
//        if($whereCreator != "")
//        {
//            $similarBooks = BookIrBook2::where(function ($query) use($whereCreator, $id){
//               $query->where('_id' ,'!=',$id)->where('xparent' , 0)->where('xrequest_manage_parent' , '!=' , 1);
//                for ($i=0 ; $i<count($whereCreator); $i++){
//                    if ($whereCreator[$i][2] == 'where'){
//                        $query->where($whereCreator[$i][0] , $whereCreator[$i][1]);
//                    }
//                    if ($whereCreator[$i][2] == 'orWhere'){
//                        $query->orWhere($whereCreator[$i][0],$whereCreator[$i][1]);
//                    }
//                }
//            })->get();
//            if($similarBooks != null)
//            {
//                foreach ($similarBooks as $similarBook)
//                {
//                    BookIrBook2::where('_id', $similarBook->_id)->update(['xparent' => $id]);
//                }
//            }
//        }
//        BookIrBook2::where('_id', $id)->where('xparent', 0)->where('xrequest_manage_parent','!=',1)->update(['xparent' => -1]);
//    }

    ///////////////////////////////////////////////Check///////////////////////////////////////////////////

//    public function check()
//    {
//        $books = BookIrBook2::where('xparent', '=', 0)->where('xrequest_manage_parent','!=',1)->orderBy('xpublishdate_shamsi', -1)->take(1)->get();
//        if($books != null)
//        {
//            foreach ($books as $book)
//            {
//                $this->initCheck($book);
//            }
//        }
//    }

    ///////////////////////////////////////////////Exist///////////////////////////////////////////////////
    public function exist(Request $request){
        $shabak = $request["shabak"];
        $publish_date = $request["publishdate"];

        if ($shabak == '' && $publish_date == ''){
            return response()->json(['error'=>'BAD REQUEST','error_code'=>'2002','result_count'=>0 , 'result'=>''], 400);
        }
        $books='';
        if ($shabak != '' && $publish_date != ''){
            $books = BookIrBook2::where('xpublishdate_shamsi',(int)$publish_date)
                ->where(function ($query) use ($shabak) {
                    $query->where('xisbn',$shabak);
                    $query->orWhere('xisbn2',$shabak);
                    $query->orWhere('xisbn3',$shabak);
                })->get();
        }
        $resultArray = array();
        if($books != ''){
            foreach($books as $book){
                $temp['id'] = $book->_id;
                $temp['title'] = $book->xname;
                $resultArray[] = $temp;
            }

        }

        $resultCount = count($resultArray);

        if($resultCount == 0){
            return response()->json(['error'=>'NOT FOUND','error_code'=>'2001','result_count'=>0 , 'result'=>''], 404);
        }else{
            return response()->json(['error'=>'','result_count'=>$resultCount ,'results'=>$resultArray]);
        }


    }
}
