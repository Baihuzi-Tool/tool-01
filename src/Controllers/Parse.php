<?php

namespace Tool01\Controllers;

use GuzzleHttp\Client;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;
use Tool01\Common\Common;

class Parse
{
    public function upload()
    {
        $ids = explode("\n", str_replace('\r', '\n', $_POST['find']));
        array_map(
            function ($id) {
                return (int)trim($id);
            },
            $ids
        );
        
        $file = $_FILES['csv'] ?? null;
        if ($file['error'] != 0) {
            echo "上传文件失败，请返回重试!";
            exit;
        }
        if ($file['type'] != "text/csv") {
            echo "暂只支持csv格式!";
            exit;
        }
        
        $csv = Reader::createFromPath($file['tmp_name'], 'r');
        //$csv->setHeaderOffset(0);
        //$csv->setDelimiter(' ');
        
        $input_bom = $csv->getInputBOM();
        
        if ($input_bom === Reader::BOM_UTF16_LE || $input_bom === Reader::BOM_UTF16_BE) {
            $csv->addStreamFilter('convert.iconv.UTF-16/UTF-8');
        }
        
        $csvFile = Writer::createFromPath('php://temp', 'w');
        
        foreach ($csv as $k => $record) {
            
            if ($k == 0) {
                if (sizeof($record) > 1) {
                    $csvFile->insertOne($record);
                }
                else {
                    $csvFile->insertOne(explode("\t", $record[0]));
                }
                
            }
            else {
                if (sizeof($record) > 1) {
                    $res = $record;
                    
                }
                else {
                    $res = explode("\t", $record[0]);
                }
                
                if (in_array((int)trim($res[0]), $ids)) {
                    //echo "1\n";
                    $csvFile->insertOne($res);
                }
            }
            //  print_r($record);
        }
        $csvFile->output("name-for-your-file.csv");
        exit;
        /*
         header('Content-Type: text/csv; charset=UTF-8');
         header('Content-Description: File Transfer');
         header('Content-Disposition: attachment; filename="name-for-your-file.csv"');
         exit;*/
    }
    
    public function get()
    {
        $arr = explode("\n", str_replace('\r', '\n', $_POST['find']));
        
        $dir = PROJECT_DIR . "/logs/";
        
        if (is_dir($dir)) {
            Common::deldir($dir);
        }
        
        mkdir($dir, 0777);
        
        $client = new Client();
        
        $topics = [];
        foreach ($arr as $k => $v) {
            $v = trim($v);
            if (!$v) {
                continue;
            }
            
            $res     = $client->get(
                'https://www.soyoung.com/p' . $v . '/',
                [
                    'headers' => [
                        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
                    ],
                ]
            );
            $content = $res->getBody()->getContents();
            
            preg_match('/<div class="c">\s*(.*)\s*<\/div>/', $content, $found);
            $c = $found[1] ?? "";
            preg_match('/<img (.*?) src=\"(.+?)\".*?>/', $c, $imgs);
            $img        = $imgs[2] ?? "";
            $imgContent = file_get_contents($img);
            file_put_contents($dir . $v . ".jpg", $imgContent, 8);
            preg_match('/<p class="text"><p>(.+?)<\/p>/', $c, $text);
            //
            //
            // $img = $imgs[2]??"";
            $title      = $text[1] ?? "";
            $topics[$v] = [
                'https://www.soyoung.com/p' . $v . '/',
                $title,
                $img,
            ];
            file_put_contents(
                $dir . "t.log",
                $v
                . "--|||--"
                . 'https://www.soyoung.com/p'
                . $v
                . '/'
                . "--|||--"
                . ($title ? : ' null ')
                . "--|||--"
                . $img
                . "\n",
                8
            );
        };
        $str = "<tr><td>id</td><td>url</td><td>title</td><td>img</td></tr>";
        foreach ($topics as $id => $topic) {
            $str .= "<tr><td>{$id}</td><td width='400px'><a href='{$topic['0']}' target='_blank'>{$topic['0']}</a></td>"
                    . "<td>{$topic['1']}</td><td><img style='max-width: 300px' src='/imgs/{$id}.jpg'></td></tr>";
        }
        echo "<html><body><table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\">"
             . $str
             . "</table></body></html>";
    }
}