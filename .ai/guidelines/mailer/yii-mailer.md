```php
<?php

/**
 * AI Guideline: Yii 2.0 Mailer Structure
 * 
 * This file serves as a reference for sending emails in Yii 2.
 * Yii supports composing and sending emails via different transport layers.
 * 
 * @see https://www.yiiframework.com/doc/api/2.0/yii-mail-mailerinterface
 */

namespace yii\mail;

use yii\base\Component;

/**
 * MailerInterface is the interface that should be implemented by mailer classes.
 */
interface MailerInterface
{
    /**
     * Creates a new message instance.
     * @return MessageInterface message instance.
     */
    public function compose($view = null, array $params = []);

    /**
     * Sends the given message.
     * @param MessageInterface $message message instance to be sent
     * @return bool whether the message has been sent successfully
     */
    public function send($message);

    /**
     * Sends multiple messages at once.
     * @param array $messages list of messages to be sent
     * @return int number of messages that are successfully sent
     */
    public function sendMultiple(array $messages);
}

/**
 * MessageInterface is the interface that should be implemented by message classes.
 */
interface MessageInterface
{
    public function setFrom($from);
    public function setTo($to);
    public function setCc($cc);
    public function setBcc($bcc);
    public function setSubject($subject);
    public function setTextBody($text);
    public function setHtmlBody($html);
    public function attach($fileName, array $options = []);
    public function send(MailerInterface $mailer = null);
}

/**
 * Example Usage
 * 
 * ```php
 * Yii::$app->mailer->compose('contact/html', ['contactForm' => $form])
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send();
 * ```
 */
class ExampleUsage {}
\n```
