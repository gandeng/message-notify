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
                $mail->CharSet = "UTF-8";
                /*阿里云默认封了25端口，需要把端口号改成587*/
                /*下面用阿里云的企业邮箱来发送*/
                $mail->Host     = $config['dns'];
                $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                $mail->Username = $config['from'];                     // SMTP username
                $mail->Password = $config['password'];
                $mail->Port = $config['port'];
                //Recipients
                $mail->addCustomHeader('Content-type: text/html; ');
                $mail->setFrom($config['from'], '唯高自研信息系统');
                $mail->addAddress($mail_to_email);     // Add a recipient
                if (!empty($acc)) $mail->addBCC($acc);//密送
                if (!empty($replay_to)) $mail->addReplyTo($replay_to, '唯高自研信息系统 快速回复');//点击快速回复到这个邮箱里


                // Attachments 附件，路径如下
                //D:\vigo-erp\public\upload\vg_15574.pdf
                // dump($att);
                if (!empty($att)) {
                    $mail->addAttachment($att, '附件');         // Add attachments
                    //                $mail->addAttachment( '../public/upload/vg_15597.pdf', 'new.pdf' );    // Optional name
                }

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $template->getTitle();//主题
                $mail->Body    = $template->getMailBody();//内容
                $mail->AltBody = $template->getMailBody();

                $mail->send();
                $issent = true;
            }


            //echo 'Message has been sent';
        } catch (Exception $e) {
//            var_dump($e->getMessage());
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $issent = false;
        }
        unset($mail);
        return $issent;
    }
    private function getQuery(string $pipeline): array{
        $config = $this->getConfig();
        $config = $config['pipeline'][$pipeline] ?? $config['pipeline'][$config['default']];
//        var_dump($config);
        return $config;
    }
}
