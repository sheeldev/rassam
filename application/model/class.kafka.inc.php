<?php

class kafka
{
    private $producer;
    private $consumer;
    protected $sheel;
    public function __construct($sheel)
    {

        $this->sheel = $sheel;
        $conf = new RdKafka\Conf();
        $conf->set('security.protocol', 'ssl');
        $conf->set('ssl.ca.location', $this->sheel->config['ssl_ca_location']);
        $conf->set('ssl.certificate.location', $this->sheel->config['ssl_certificate_location']);

        $this->producer = new RdKafka\Producer($conf);
        $this->producer->addBrokers($this->sheel->config['kafka_brokers']);

        $this->consumer = new RdKafka\Consumer($conf);
        $this->consumer->addBrokers($this->sheel->config['kafka_brokers']);

    }

    
    public function produce($message, $topic, $partition = 0, $key=null, $headers = null)
    {
        try {
            $topic = $this->producer->newTopic($topic);
            $topic->producev($partition, 0, json_encode($message),$key, $headers);
            $this->producer->poll(0);
            $this->producer->flush(10000);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    public function consume($topic, $partition = 0)
    {
        $kafkatopic = $this->consumer->newTopic($topic);
        $kafkatopic->consumeStart($partition, RD_KAFKA_OFFSET_BEGINNING);
        while (true) {
            try {
                $message = $kafkatopic->consume($partition, 1000);
                switch ($message->err) {
                    case RD_KAFKA_RESP_ERR_NO_ERROR:
                        echo $message->payload;
                    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                        echo "No more messages; will wait for more\n";
                        break;
                    case RD_KAFKA_RESP_ERR__TIMED_OUT:
                        echo "Timed out\n";
                        break;
                    default:
                        throw new \Exception($message->errstr(), $message->err);
                        break;
                }
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
    }
}