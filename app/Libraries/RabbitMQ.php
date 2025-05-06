<?php
/**
 * @Notes: RabbitMQ 工具类
 * @Date: 2024/3/15
 * @Time: 15:53
 * @Interface RabbitMQTool
 * @return
 */


namespace App\Libraries;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{

    private static  $_instance;
    private  $connection;
    private $channel;

    public static function getInstance(){

        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function __construct(){
        try {
            $config = [
                'host' => env('RABBITMQ_HOST',''),
                'port' => env('RABBITMQ_PORT',''),
                'user' => env('RABBITMQ_USER',''),
                'password' => env('RABBITMQ_PASSWORD',''),
                'vhost' =>  env('RABBITMQ_VHOST',''),
            ];
            return $this->connection = new AMQPStreamConnection($config['host'],$config['port'],$config['user'],$config['password'],$config['vhost']);
        }catch (\Throwable $exception){
            throw new \Exception('连接RabbitMQ失败');
        }

    }

    /** 连接
     * @return AMQPStreamConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 信道
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Notes: 发送消息
     * Date: 2024/3/15 15:59
     * @param $queue  队列
     * @param $exchange 交换机
     * @param $routingKey
     * @param $messageBody 消息体
     * @param string $exchangeDirect
     * @return bool
     * @throws \Exception
     */
    public function push( $queue, $exchange, $routingKey, $messageBody, $exchangeDirect = 'direct')
    {
        try {
            //构建通道（mq的数据存储与获取是通过通道进行数据传输的）
            $channel = $this->connection->channel();
            //声明一个队列
            $channel->queue_declare($queue, false, true, false, false);
            //指定交换机，若是路由的名称不匹配不会把数据放入队列中
            $channel->exchange_declare($exchange,$exchangeDirect,false,true,false);
            //队列和交换器绑定/绑定队列和类型
            $channel->queue_bind($queue,$exchange,$routingKey);

            $config = [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ];

            //实例化消息推送类
            $message = new AMQPMessage($messageBody,$config);

            //消息推送到路由名称为$exchange的队列当中
            $channel->basic_publish($message,$exchange,$routingKey);

            //关闭消息推送资源
            $channel->close();
            //关闭mq资源
            $this->connection->close();
            return true;
        }catch (\Throwable $exception){
            throw new \Exception('发送异常');
        }
    }

    /**
     * Notes: 消费者：取出消息进行消费，并返回
     * Date: 2024/3/15 16:01
     * @param $queue
     * @param $callback
     * @return true
     * @throws \Exception
     */
    public  function pop($queue, $callback)
    {
        try {
            //连接到 RabbitMQ 服务器并打开通道
            $channel = $this->connection->channel();
            //声明要获取内容的队列
            $channel->queue_declare($queue, false, true, false, false);

            //获取队列中的下一条消息
            $msg = $channel->basic_get($queue);
            //消息主题返回给回调函数
            if (!empty($msg)){
                $res = $callback($msg->body);
                if($res) $channel->basic_ack($msg->getDeliveryTag());
            }
            $channel->close();
            $this->connection->close();
            return true;
        }catch (\Throwable $exception){
            throw new \Exception('消费异常');
        }
    }

    /**
     * 获取队列消息
     * @param $queue
     * @return string
     * @throws \Exception
     */
    public function getMessage($queue){
        //连接到 RabbitMQ 服务器并打开通道
        $this->channel = $channel = $this->connection->channel();
        //声明要获取内容的队列
        $channel->queue_declare($queue, false, true, false, false);
        //获取队列中的下一条消息
        return $channel->basic_get($queue);
    }

    /**
     * 回应
     * @param $msg
     * @return bool
     * @throws \Exception
     */
    public function ack($msg){
        $this->channel->basic_ack($msg->getDeliveryTag());
        $this->channel->close();
        $this->connection->close();
        return true;
    }

    /**
     * 获取队列信息(消息总条数)
     * @param $queue
     * @return mixed
     */
    public function getMessageCount($queue) {

        return $this->connection->channel()->queue_declare($queue, true)[1];
    }


    public function sendMqMsg($queue,$exchangeName,$bodyMsg)
    {
        try {

            $bodyMsgJson = $bodyMsg;
            if (is_array($bodyMsg)) $bodyMsgJson = json_encode($bodyMsg);

            $this->connection->channel()->queue_declare($queue, false, true, false,false);
            $msg = new AMQPMessage($bodyMsgJson);
            $this->connection->channel()->queue_bind($queue,$exchangeName,$queue);
            $this->connection->channel()->basic_publish($msg,$exchangeName,$queue);

            return true;

        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

    }
}
