<?php

declare(strict_types=1);

namespace MessageNotify\Channel;

use MessageNotify\Template\AbstractTemplate;
use PHPMailer\PHPMailer\PHPMailer;

class MailChannel extends AbstractChannel
{
    public function send(AbstractTemplate $template)
    {
        // TODO: Implement send() method.
//        var_dump('配置文件：');
//        var_dump($template);
        $config = $this->getQuery($template->getPipeline());
//        var_dump($config);
        $mail_to_email_str = $template->getTo();
        $issent            = false;
        try {
            $mail_to_email_array = explode(',', $mail_to_email_str);
            foreach ($mail_to_email_array as $mail_to_email) {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->CharSet  = "UTF-8";
                $mail->Priority = 1;
                $mail->Host     = $config['dns'];
                $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                $mail->Username = $config['from'];                     // SMTP username
                $mail->Password = $config['password'];
                $mail->Port     = $config['port'];
                //Recipients
//                $mail->addCustomHeader('Content-type: text/html; ');
                $mail->setFrom($config['from'], 'Lasfit');
                $mail->addAddress($mail_to_email);     // Add a recipient
                if (!empty($acc)) $mail->addBCC($acc);//密送
                if (!empty($replay_to)) $mail->addReplyTo($replay_to, 'Lasfit');//点击快速回复到这个邮箱里

                $mail->Subject = $template->getTitle();//主题
                $mail->Body    = $template->getMailBody();//内容
                $mail->AltBody = $template->getMailBody();
                if (!empty($template->getAt())) {
                    foreach ($template->getAt() as $att) {
                        $pdf_name = empty($pdf_name) ? 'att.pdf' : $pdf_name;
                        $mail->addAttachment($att, $pdf_name);
                    }
                    //                $mail->addAttachment( '../public/upload/vg_15597.pdf', 'new.pdf' );    // Optional name
                }

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML

                $mail->send();
                $issent = true;
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $issent = false;
        }
        unset($mail);
        return $issent;
    }

    private function getQuery(string $pipeline): array
    {
        $config = $this->getConfig();
        $config = $config['pipeline'][$pipeline] ?? $config['pipeline'][$config['default']];
//        var_dump($config);
        return $config;
    }


}