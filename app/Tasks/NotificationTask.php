<?php
namespace App\Tasks;

@error_reporting(E_ALL & ~E_NOTICE);

use App\Module\Base;
use App\Module\Chat;
use App\Module\Umeng;
use App\Module\Users;
use DB;
use Hhxsv5\LaravelS\Swoole\Task\Task;

class NotificationTask extends Task
{
    private $contentId;

    /**
     * NotificationTask constructor.
     * @param int $contentId
     */
    public function __construct($contentId)
    {
        $this->contentId = intval($contentId);
    }

    public function handle()
    {
        $row = Base::DBC2A(DB::table('chat_msg')->where('id', $this->contentId)->first());
        if (empty($row)) {
            return;
        }
        if ($row['roger']) {
            return;
        }
        //
        $username = $row['receive'];
        $message = Base::string2array($row['message']);
        $lists = Base::DBC2A(DB::table('umeng')->where('username', $username)->get());
        foreach ($lists AS $item) {
            Umeng::notification($item['platform'], $item['token'], Users::nickname($username), Chat::messageDesc($message), [
                'notifyType' => 'userMsg',
                'contentId' => $this->contentId,
                'username' => $username,
            ]);
        }
    }
}
