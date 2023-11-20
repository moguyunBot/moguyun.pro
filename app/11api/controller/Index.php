<?php
namespace app\api\controller;

use app\common\model\User;

class Index extends \app\BaseController{
    public $bot;
    
    public function init(){
        // $url = 'https://api.telegram.org/bot'.config('telegram.token').'/setWebhook';
        // $res = curl($url,http_build_query([
        //     'url'       =>  'https://api.moguyun.pro',
        // ]));
        // dump($res);
    }
    
    public function index(){
        file_put_contents(root_path().'api.txt',date('Y-m-d H:i:s').PHP_EOL.file_get_contents('php://input').PHP_EOL,FILE_APPEND);
        $param = '{"update_id":428692137,
"message":{"message_id":10,"from":{"id":6431449029,"is_bot":false,"first_name":"\u673a\u5668\u4eba\u5f00\u53d1","username":"bot_kf","language_code":"zh-hans"},"chat":{"id":6431449029,"first_name":"\u673a\u5668\u4eba\u5f00\u53d1","username":"bot_kf","type":"private"},"date":1697090407,"text":"TRX\u5151\u6362\u673a\u5668\u4eba"}}';
        $param = file_get_contents('php://input');
        $param = json_decode($param,true);
        $this->bot = new \bot\BotApi(config('telegram.token'));
        try{
            if(!empty($param['message']['reply_to_message'])){
                $this->replyMessage($param['message']);
            }else if(!empty($param['message']['text'])){
                $this->onMessage($param['message']);
            }else if(!empty($param['callback_query'])){
                $this->onCallback($param['callback_query']);
            }else if(!empty($param['message']['new_chat_members'])){
                if($param['message']['new_chat_members'][0]['id']==$bot_id){
                    $this->bot->sendMessage($param['message']['chat']['id'],'当前群ID:`'.$param['message']['chat']['id'].'`','Markdown');
                }
            }
        }catch(\Exception $e){
            echo "Exception: " . $e->getMessage() . "<br>";
            echo "Stack Trace:<br>";
            foreach ($e->getTrace() as $entry) {
                if(!empty($entry['file']))
                echo "File: " . $entry['file'] . "<br>";
                if(!empty($entry['line']))
                echo "Line: " . $entry['line'] . "<br>";
                if(!empty($entry['function']))
                echo "Function: " . $entry['function'] . "<br>";
                echo "<br>";
            }
        }
    }
    
    private function start($type,$chat,$from,$message_id){
        $user = User::where(['from_id'=>$from['id']])->find();
        if(!$user){
            $user = User::create([
                'from_id'       =>  $from['id'],
                'nickname'      =>  ($from['first_name']??'').($from['last_name']??''),
                'username'      =>  $from['username']??'',
                'invitation'    =>  nonce_str(10),
            ]);
        }
        $text = '你好，欢迎使用TRX兑换机器人管家';
        $this->bot->sendMessage([
            'chat_id'       =>  $chat['id'],
            'text'          =>  $text,
            'parse_mode'    =>  'Markdown',
            'reply_markup'  =>  [
                'inline_keyboard'      =>  [
                    [
                        ['text'=>'TRX兑换机器人','callback_data'=>'TRX兑换机器人']
                    ]
                ],
                'resize_keyboard'   =>  true,
            ]
        ]);
    }
    
    private function onMessage($message){
        $chat_id = $message['chat']['id'];
        $from_id = $message['from']['id'];
        $message_id = $message['message_id'];
        if(substr($message['text'],0,6)=='/start'){
            $this->start('sendMessage',$message['chat'],$message['from'],$message_id);
            return;
        }
    }
    
    private function onCallback($callback){
        $message = $callback['message'];
        $chat_id = $message['chat']['id'];
        $from_id = $callback['from']['id'];
        $message_id = $message['message_id'];
        $callback_id = $callback['id'];
        
        if($callback['data']=='deleteMessage'){
            $this->bot->deleteMessage(
                $chat_id,
                $message_id,
            );
            return;
        }
        //点击关键词管理
        else if($callback['data']=='TRX兑换机器人'){
            $this->bot->sendMessage([
                'chat_id'   =>  $chat_id,
                'text'      =>  'TRX兑换机器人',
                'reply_markup'  =>  [
                    'inline_keyboard'   =>  [
                        [
                            ['text'=>'设置欢迎信息','web_app'=>['url'=>(string)addons_url('trxdh://index/setstart',[],false,'www')]]
                        ]
                    ]
                ]
            ]);
        }
    }
    
    private function replyMessage($message){
        $chat_id = $message['chat']['id'];
        $from_id = $message['from']['id'];
        $message_id = $message['message_id'];
        //回复添加的关键词
        if($message['reply_to_message']['text']=='添加关键词'){
            if(Db::name('keywords')->where(['from_id'=>$from_id,'keyword'=>$message['text']])->find()){
                $this->bot->sendMessage($chat_id,$message['text'].' 已存在');
            }else if(Db::name('keywords')->insert(['from_id'=>$from_id,'keyword'=>$message['text']])){
                $this->bot->sendMessage($chat_id,$message['text'].' 添加成功');
            }
            $keywords = Db::name('keywords')->where(['from_id'=>$from_id])->order('id desc')->select();
            $text = "*关键词管理*\n";
            foreach($keywords as $v){
                $text .= $v['keyword'].PHP_EOL;
            }
            $text .= "点击➕添加关键词\n点击➖添加关键词";
            $replyMarkup = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
                [
                    ['text'=>'➕','callback_data'=>'添加关键词'],
                    ['text'=>'➖','callback_data'=>'删除关键词']
                ],
                [
                    ['text'=>'返回主页','callback_data'=>'返回主页'],
                ]
            ]);
            $this->bot->sendMessage($chat_id,$text,'Markdown',false,null,$replyMarkup);
            $this->处理缓存($from_id);
        }
        //回复删除的关键词
        else if($message['reply_to_message']['text']=='删除关键词'){
            $keyword = Keywords::where(['from_id'=>$from_id,'keyword'=>$message['text']])->find();
            if(!$keyword){
                $this->bot->sendMessage($chat_id,$message['text'].' 不存在');
            }else{
                $keyword->delete();
                $this->bot->sendMessage($chat_id,$message['text'].' 删除成功');
            }
            $keywords = Db::name('keywords')->where(['from_id'=>$from_id])->order('id desc')->select();
            $text = "*关键词管理*\n";
            foreach($keywords as $v){
                $text .= $v['keyword'].PHP_EOL;
            }
            $text .= "点击➕添加关键词\n点击➖添加关键词";
            $replyMarkup = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
                [
                    ['text'=>'➕','callback_data'=>'添加关键词'],
                    ['text'=>'➖','callback_data'=>'删除关键词']
                ],
                [
                    ['text'=>'返回主页','callback_data'=>'返回主页'],
                ]
            ]);
            $this->bot->sendMessage($chat_id,$text,'Markdown',false,null,$replyMarkup);
            $this->处理缓存($from_id);
        }
        //回复卡密
        else if($message['reply_to_message']['text']=='输入卡密'){
            $kami = Kami::where(['code'=>$message['text']])->find();
            if(!$kami){
                $text = '卡密不存在';
            }else if($kami['status']==1){
                $text = '卡密已被使用';
            }else{
                $user = User::where(['from_id'=>$from_id])->find();
                $user->end_time = $user['end_time']+(86400*$kami['day']);
                $user->save();
                $kami->save(['status'=>1]);
                $text = '续费成功'.PHP_EOL.'到期时间:'.date('Y-m-d H:i:s',$user->end_time);
            }
            $this->处理缓存($from_id);
            $this->bot->sendMessage($chat_id,$text);
            return;
        }
        //回复推送群ID
        else if($message['reply_to_message']['text']=='输入推送群ID'){
            try{
                $this->bot->getChatMember($message['text'],$this->bot_id);
                User::where(['from_id'=>$from_id])->update(['push_id'=>trim($message['text'])]);
                $text = '设置成功'.PHP_EOL;
                $text .= "推送群ID:".trim($message['text']);
                $replyMarkup = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
                    [
                        ['text'=>'返回主页','callback_data'=>'返回主页'],
                    ]
                ]);
                $this->bot->sendMessage($chat_id,$text,'Markdown',false,null,$replyMarkup);
                $this->处理缓存($from_id);
            }catch(\Exception $e){
                $this->bot->sendMessage($chat_id,'推送群ID错误');
            }
        }
    }
    
    public function 处理缓存($from_id){
        $push_ids = User::column('push_id');
        Cache::set('push_ids',$push_ids);
        $keywords = Keywords::order('id desc')->select();
        $keywords->append(['push_id','end_time']);
        
        if(count($keywords)){
            Cache::set('keywords',$keywords->toArray());
        }else{
            Cache::set('keywords',[]);
        }
    }
}