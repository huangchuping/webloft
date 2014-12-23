<?php

/**
 * 邮件发送类
 */
class Mail
{
    // SMTP服务器名称
    private $SmtpHost;
    /* SMTP服务端口
    + 标准服务端口，默认为25
    */
    private $SmtpPort = 25;
    // SMTP用户名
    private $SmtpUser = '';
    // SMTP用户密码
    private $SmtpPassword = '';
    /* 超时时间
     + 用于fsockopen()函数，超过该时间未连上则中断
     */
    private $TimeOut = 30;
    /* 用户身份
     + 用于HELO指令
     */
    private $HostName = 'localhost';
    /* 开启调试模式 */
    private $Debug = false;
    /* 是否进行身份验证 */
    private $Authentication = false;
    /* Private Variables */
    private $Socket = false;

    /**
     * 构造方法
     */
    public function __construct($smtpHost = '', $smtpUser = '', $smtpPassword = '', $authentication = false, $smtpPort = 25)
    {
        $this->SmtpHost = $smtpHost;
        $this->SmtpPort = $smtpPort;
        $this->SmtpUser = $smtpUser;
        $this->SmtpPassword = $smtpPassword;
        $this->Authentication = $authentication;
    }

    /** 发送邮件
     * @param string $maiTo 收件人
     * @param string $mailFrom 发件人（支持名称:Email）
     * @param string $subject 主题
     * @param string $body 内容
     * @param string $mailType 邮件类型
     * @param string $cc 抄送邮件地址
     * @param string $bcc 隐藏抄送邮件地址
     * @param string $additionalHeaders 附加内容
     * @return boolean
     */
    public function SendMail($maiTo, $mailFrom, $subject = '', $body = '', $mailType = 'HTML', $cc = '', $bcc = '', $additionalHeaders = '')
    {

        $header = '';
        $header .= "MIME-Version:1.0\r\n";
        if ($mailType == 'HTML') {
            $header .= "Content-Type:text/html;";
        }
        $header .= "charset='utf-8'\r\n";
        $header .= "To: " . $maiTo . "\r\n";
        if ($cc != '') {
            $header .= "Cc: " . $cc . "\r\n";
        }
        $header .= "From:" . $mailFrom . "<" . $mailFrom . ">\r\n";
        $header .= "Subject: " . $subject . "\r\n";
        $header .= $additionalHeaders;
        $header .= "Date: " . date("r") . "\r\n";
        $header .= "X-Mailer:By Redhat (PHP/" . phpversion() . ")\r\n";
        list($msec, $sec) = explode(' ', microtime());
        $header .= "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec * 1000000) . "." . $mailFrom . ">\r\n";

        //收件人地址解析
        $maiTo = explode(",", $maiTo);
        if ($cc != "") {
            $maiTo = array_merge($maiTo, explode(",", $cc));
        }
        if ($bcc != "") {
            $maiTo = array_merge($maiTo, explode(",", $bcc));
        }

        //邮件是否发送成功标志
        $mailSent = true;
        foreach ($maiTo as $value) {
            if (!$this->SmtpSockopen($value)) {
                $this->SmtpDebug("[错误]: 无法发送邮件至 \"" . $value . "\"\n");
                $mailSent = false;
                continue;
            }
            if ($this->SmtpSend($this->HostName, $mailFrom, $value, $header, $body)) {
                $this->SmtpDebug("[成功]: E-mail已经成功发送至 <" . $value . ">\n");
            } else {
                $this->SmtpDebug("[失败]: E-mail无法发送至 <" . $value . ">\n");
                $mailSent = false;
            }
            fclose($this->Socket);
            //$this->SmtpDebug("[失败]:  连接服务器失败\n");
        }
        $this->SmtpDebug($header);
        return $mailSent;
    }

    /**
     * 发送邮件
     * @param $helo
     * @param $maiFrom
     * @param $maiTo
     * @param $header
     * @param string $body
     * @return bool
     */
    function SmtpSend($helo, $maiFrom, $maiTo, $header, $body = "")
    {
        //发送连接命令
        if (!$this->SmtpPutcmd("HELO", $helo)) {
            return $this->SmtpError("发送 HELO 命令");
        }

        //身份验证
        if ($this->Authentication) {
            if (!$this->SmtpPutcmd("AUTH LOGIN", base64_encode($this->SmtpUser))) {
                return $this->SmtpError("发送 HELO 命令");
            }

            if (!$this->SmtpPutcmd("", base64_encode($this->SmtpPassword))) {
                return $this->SmtpError("发送 HELO 命令");
            }
        }

        //发件人信息
        if (!$this->SmtpPutcmd("MAIL", "FROM:<" . $maiFrom . ">")) {
            return $this->SmtpError("发送 MAIL FROM 命令");
        }

        //收件人信息
        if (!$this->SmtpPutcmd("RCPT", "TO:<" . $maiTo . ">")) {
            return $this->SmtpError("发送 RCPT TO 命令");
        }

        //发送数据
        if (!$this->SmtpPutcmd("DATA")) {
            return $this->SmtpError("发送 DATA 命令");
        }

        //发送消息
        if (!$this->SmtpMessage($header, $body)) {
            return $this->SmtpError("发送 信息");
        }

        //发送EOM
        if (!$this->SmtpEom()) {
            return $this->SmtpError("发送 <CR><LF>.<CR><LF> [EOM]");
        }

        //发送退出命令
        if (!$this->SmtpPutcmd("QUIT")) {
            return $this->SmtpError("发送 QUIT 命令");
        }

        return true;
    }

    /** 发送SMTP命令
     * @param $cmd
     * @param $arg
     * @return bool
     */
    function SmtpPutcmd($cmd, $arg = "")
    {
        if ($arg != '') {
            if ($cmd == '') {
                $cmd = $arg;
            } else {
                $cmd = $cmd . ' ' . $arg;
            }
        }
        fputs($this->Socket, $cmd . "\r\n");
        $this->SmtpDebug("> " . $cmd . "\n");
        return $this->SmtpOk();
    }

    /** SMTP错误信息
     * @param string $string 错误信息
     * @return bool
     */
    function SmtpError($string)
    {
        $this->SmtpDebug("[错误]: 在 " . $string . " 时发生了错误\n");
        return false;
    }

    /** SMTP信息
     * @param string $header 头部消息
     * @param string $body 内容
     * @return bool
     */
    function SmtpMessage($header, $body)
    {
        fputs($this->Socket, $header . "\r\n" . $body);
        $this->SmtpDebug("> " . str_replace("\r\n", "\n" . "> ", $header . "\n> " . $body . "\n> "));
        return true;
    }

    /* EOM */
    function SmtpEom()
    {
        fputs($this->Socket, "\r\n.\r\n");
        $this->SmtpDebug(". [EOM]\n");
        return $this->SmtpOk();
    }

    /* SMTP OK */
    function SmtpOk()
    {
        $response = str_replace("\r\n", "", fgets($this->Socket, 512));
        $this->SmtpDebug($response . "\n");

        if (preg_match("/^[23]/", $response) == 0) {
            fputs($this->Socket, "QUIT\r\n");
            fgets($this->Socket, 512);
            $this->SmtpDebug("[错误]: 服务器返回 \"" . $response . "\"\n");
            return false;
        }
        return true;
    }

    /* debug
     * @param string $message 错误消息
     */
    private function SmtpDebug($message)
    {
        if ($this->Debug) {
            echo $message . "<br />";
        }
    }

    /** 网络Socket链接准备
     * @param string $address 邮件地址
     * @return boolean
     */
    private function SmtpSockopen($address)
    {
        if ($this->SmtpHost == '') {
            return $this->SmtpSockopenMx($address);
        } else {
            return $this->SmtpSockopenRelay($this->SmtpHost);
        }
    }

    /** 获取MX记录
     * @param string $address 邮件地址
     * @return boolean
     */
    private function SmtpSockopenMx($address)
    {
        $domain = ereg_replace("^.+@([^@]+)$", "\\1", $address);
        if (!$this->MyCheckdnsrr($domain, 'mx')) {
            $this->SmtpDebug("[错误]: 无法找到 MX记录 \"" . $domain . "\"\n");
            return false;
        }
        $this->SmtpSockopenRelay($domain);
        $this->SmtpDebug('[错误]: 无法连接 MX主机 (' . $domain . ")\n");
        return false;
    }

    /** 打开网络Socket链接
     * @param string $smtpHost 服务器名称
     * @return boolean
     */
    private function SmtpSockopenRelay($smtpHost)
    {
        $this->SmtpDebug('[操作]: 尝试连接 "' . $smtpHost . ':' . $this->SmtpPort . "\"\n");
        $this->Socket = @stream_socket_client('tcp://' . $smtpHost . ':' . $this->SmtpPort, $errno, $errstr, $this->TimeOut);
        if (!($this->Socket && $this->SmtpOk())) {
            $this->SmtpDebug('[错误]: 无法连接服务器 "' . $smtpHost . "\n");
            $this->SmtpDebug('[错误]: ' . $errstr . " (" . $errno . ")\n");
            return false;
        }
        $this->SmtpDebug('[成功]: 成功连接 ' . $smtpHost . ':' . $this->SmtpPort . "\"\n");
        return true;;
    }

    /** 自定义邮箱有效性验证
     * + 解决window下checkdnsrr函数无效情况
     * @param string $hostName 主机名
     * @param string $recType 类型（可以是MX、NS、SOA、PTR、CNAME 或 ANY）
     * @return boolean
     */
    function MyCheckdnsrr($hostName, $recType = 'MX')
    {
        if ($hostName != '') {
            $recType = $recType == '' ? 'MX' : $recType;
            exec("nslookup -type=$recType $hostName", $result);
            foreach ($result as $line) {
                if (preg_match("/^$hostName/", $line) > 0) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
}


//test

//邮件配置
$title = '考勤数据脚本运行情况抽取';
$sm = new Mail('mail.7sef.com', 'itadmin', 'abc,.123');
$sendTo = "huangcp@7sef.com,duhz@7sef.com,yinhl@7sef.com,zhangtt@7sef.com,luoy@7sef.com,zhanghy@7sef.com";
if ($result['status']) {
    $content = "<table style='background-color: #CCCCCC;' cellspacing='1' cellpadding='3'  width='99%' >";
    $content .= "<tr height='30'><td bgcolor='#FFFFFF'>信息类型：</td> <td bgcolor='#FFFFFF'> <strong style='color:#F00; size:14px;' >职能部门员工" . date("Y 年 m 月", strtotime("-1 month")) . "考勤数据抽取</strong></td>";
    $content .= "<tr height='30' ><td width='10%' bgcolor='#FFFFFF'>考勤系统数据：</td><td width='40%' bgcolor='#FFFFFF'>成功写入hr_attendance_sum表:" . $totalSum[0]['total'] . "条数据</td>";
    $content .= "<tr height='30' ><td width='10%' bgcolor='#FFFFFF'>考勤明细数据：</td><td width='40%' bgcolor='#FFFFFF'>成功写入hr_attendence_check表:" . $totalCheck[0]['total'] . "条数据</td></tr></table>";
} else {
    if ($result['message'] == '写入hr_attendance_sum出错了') {
        $content = "<table style='background-color: #CCCCCC;' cellspacing='1' cellpadding='3'  width='99%' >";
        $content .= "<tr height='30'><td bgcolor='#FFFFFF'>信息类型：</td> <td bgcolor='#FFFFFF'> <strong style='color:#F00; size:14px;' >职能部门员工" . date("Y 年 m 月", strtotime("-1 month")) . "考勤数据抽取</strong></td>";
        $content .= "<tr height='30' ><td width='10%' bgcolor='#FFFFFF'>信息反馈：</td><td width='40%' bgcolor='#FFFFFF'>写入hr_attendence_check表失败</td></tr></table>";
    } elseif ($result['message'] == '该月数据已经保存过了') {
        $content = "<table style='background-color: #CCCCCC;' cellspacing='1' cellpadding='3'  width='99%' >";
        $content .= "<tr height='30'><td bgcolor='#FFFFFF'>信息类型：</td> <td bgcolor='#FFFFFF'> <strong style='color:#F00; size:14px;' >职能部门员工" . date("Y 年 m 月", strtotime("-1 month")) . "考勤数据抽取</strong></td>";
        $content .= "<tr height='30' ><td width='10%' bgcolor='#FFFFFF'>信息反馈：</td><td width='40%' bgcolor='#FFFFFF'>该月数据已经保存过了</td></tr></table>";
    }
}
//	$end = $sm->SendMail($sendTo,'itadmin@7sef.com',$title,$content);

