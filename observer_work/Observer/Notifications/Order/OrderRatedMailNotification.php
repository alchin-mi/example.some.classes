<?php

namespace Compo\Observer\Notifications\Order;

class OrderRatedMailNotification extends OrderNotification
{
    private $emailTo = ['support@promrukav.ru'];
    private $template = '/mail/order_rated_mail';
    protected $logName = 'orders_rating';
    protected $logFile = MAIN_DIR.'/log/orders_rated_mail_notification.log';

    public function process($event)
    {
        parent::process($event);
        $context = $this->data['content'];
        $context['rating'] = $event->rating;
        $context['comment'] = $event->comment;
        unset($context['orderdata']);
        unset($context['userdata']);
        unset($context['customerInfo']);
        unset($context['managerInfo']);
        unset($context['specialistInfo']);
        $this->writeLog($context);
    }

    protected function trySendNotification(): string
    {
        $this->emailTo[] = $this->data['content']['specialistInfo']['email'];
        if ($this->data['event']->rating < 4) {
            return $this->prepareToSend(
                $this->emailTo,
                "Неудовлетворительная оценка заказа №" . $this->data['content']['number1c'],
                $this->template,
                $this->data
            );
        } else {
            return "Письмо не отправлено, заказ №{$this->data['content']['number1c']} не соответствует условию";
        }
    }
}
