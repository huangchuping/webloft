<?php
/**
 * @description HR 考勤数据同步汇总
 * @author huangchupin
 * @date 2014-11-20
 * @审核人:${user}
 * @审核时间:${date} ${time}
 * @修改人:
 * @修改时间 :
 * @修改备注:
 */

header("Content-type: text/html; charset=utf-8");
ini_set("max_execution_time", 1800);

class Attendance
{
    private $dbMysql; //MYSQL
    private $dbSqlServer; //sql server

    public function __construct()
    {
        $this->connectDb();
    }

    /**
     * 连接数据库
     */
    private function connectDb()
    {
        //连接mysql数据库
//        $this->dbMysql = new PDO('mysql:host=10.0.1.42;dbname=cb_php', 'root', 'adminadmin');
        $this->dbMysql = new PDO('mysql:host=10.0.1.120;dbname=rootdb', 'myeip', 'cc&0055cb');
        $this->dbMysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dbMysql->exec('set names utf8');

        //连接SQL server数据库
        $this->dbSqlServer = new PDO("odbc:Driver=sql server;Server=10.0.1.27;Database=WebAttend1;", 'sa', '');
        $this->dbSqlServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 查询职能部门ID
     */
    private function getDepts()
    {
        $sql = "SELECT dept_id,dept_sn,dept_name FROM hr_dept WHERE dept_type = '06'";
        $depts = $this->dbMysql->query($sql);
        $deptId = '(';
        foreach ($depts as $key => $item) {
            if ($key > 0) {
                $deptId .= ",'" . $item['dept_id'] . "'";
            } else {
                $deptId .= "'" . $item['dept_id'] . "'";
            }
        }
        $deptId .= ')'; //获取职能部门ID
        return $deptId;
    }

    /**
     * 查询职能员工SN
     */
    private function getEmps($deptId)
    {
        $sql1 = "SELECT emp_sn,emp_cn_name,emp_dept FROM hr_employee WHERE emp_dept in " . $deptId;
        $emps = $this->dbMysql->query($sql1);
        $empSn = '(';
        foreach ($emps as $key => $row) {
            if ($key > 0) {
                $empSn .= ",'" . $row['emp_sn'] . "'";
            } else {
                $empSn .= "'" . $row['emp_sn'] . "'";
            }
        }
        $empSn .= ')';
        return $empSn;
    }

    /**
     * 获取上月的第一天和最后一天
     * @return array
     */
    private function getForwardMoth()
    {
        $forward['first'] = date("Y-m-d",strtotime("first day of last month"));
        $forward['last'] = date("Y-m-d",strtotime("last day of last month"));
        return $forward;
    }

    /**
     * 获得人员的考勤明细
     * @param $empSn
     * @param $forwardMonth
     * @return mixed
     */
    private function getWorkAttendance($empSn, $forwardMonth)
    {
        $sql = "select empno,recdate, MAX(RecTime) AS endtime, MIN(rectime) as starttime from v_atdrecord_qsf
                where empno in " . $empSn . " and recdate between '" . $forwardMonth['first'] . "' and '" . $forwardMonth['last'] . "'
                GROUP BY empno,recdate";
        $result = $this->dbSqlServer->query($sql);
        return $result->fetchAll();
    }

    /**
     * 获取员工总打卡天数和异常天数
     * @param $result
     * @return array
     */
    private function getTotal($result)
    {
        sort($result);
        foreach ($result as $item) {
            $empno = $item['empno'];
            unset($item['empno']);
            if (!isset($items[$empno])) {
                $items[$empno] = array('empno' => $empno, 'items' => array());
            }
            $items[$empno]['items'][] = $item;
        }
        $total = array();
        $amTime = '10:00';
        $pmTime = '17:00';
        foreach ($items as $key => $item) {
            $key = trim($key);
            $total[$key]['empSn'] = $key;
            $total[$key]['errorCount'] = 0;
            $total[$key]['item'] = serialize($item['items']);
            $total[$key]['totalCount'] = sizeof($item['items']);
            $errorDay = 0;
            foreach ($item['items'] as $val) {
                $total[$key]['year'] = substr($val['recdate'], 0, 4);
                $total[$key]['month'] = substr($val['recdate'], 5, 2);
                //计算异常打卡天数
                if (strtotime($val['starttime']) > strtotime('09:00') || strtotime($val['endtime']) < strtotime('18:00')) {
                    $total[$key]['errorCount']++;
                    $total[$key]['errorInfo'][] = $val;
                }

                //计算有效打卡天数
                switch ($val) {
                    //打卡时间在10点以后并且下班时间在18:00以后 算半天
                    case strtotime($val['starttime']) >= strtotime($amTime) && strtotime($val['starttime']) <= strtotime($pmTime) && strtotime($val['endtime']) >= strtotime('18:00'):
                        //打卡时间在10点以后并且时间段在3小时以上 算半天
                    case strtotime($val['starttime']) > strtotime($amTime) && ((strtotime($val['endtime']) - strtotime($val['starttime'])) > 10800) && strtotime($val['endtime']) < strtotime($pmTime):
                        //打卡时间在09：00以前并且下班时间在3小时以后 算半天
                    case strtotime($val['starttime']) <= strtotime('09:00') && ((strtotime($val['endtime']) - strtotime('09:00')) > 10800) && strtotime($val['endtime']) < strtotime($pmTime):
                        //打卡时间在09：00以后10:00以前并且下班时间在3小时以后 算半天
                    case strtotime($val['starttime']) > strtotime('09:00') && strtotime($val['starttime']) < strtotime($amTime) && ((strtotime($val['endtime']) - strtotime($val['starttime'])) > 10800) && strtotime($val['endtime']) < strtotime($pmTime):
                        $errorDay += 0.5;
                        break;
                    case (strtotime($val['endtime']) - strtotime($val['starttime'])) < 10800:
                    case strtotime($val['starttime']) <= strtotime('09:00') && ((strtotime($val['endtime']) - strtotime('09:00')) < 10800):
                    case strtotime($val['starttime']) > strtotime($amTime) && ((strtotime($val['endtime']) - strtotime('09:00')) < 10800):
                    case strtotime($val['starttime']) > strtotime('09:00') && strtotime($val['starttime']) < strtotime($amTime) && ((strtotime($val['endtime']) - strtotime($val['starttime'])) < 10800) && strtotime($val['endtime']) < strtotime($pmTime):
                    case strtotime($val['starttime']) > strtotime('18:00') && strtotime($val['endtime']) > strtotime('18:00') :
                    case strtotime($val['starttime']) >= strtotime('09:00') && strtotime($val['endtime']) <= strtotime('18:00') && (strtotime($val['endtime']) - strtotime($val['starttime'])) < 10800:
                        $errorDay += 1;
                        break;
                }
            }
            $total[$key]['useforDays'] = floatval($total[$key]['totalCount']) - floatval($errorDay);
        }
        return $total;
    }

    /**
     * 将sqlserver导出数据转存至本地数据库
     * @param $total
     * @return bool
     */
    public function inputAttendance($total)
    {
        $result = array();
        $search = "select count(*) as total from hr_attendance_sum where `attendance_year` = " . $total['000001']['year'] . " and `attendance_month` = " . $total['000001']['month'];
        $valid = $this->dbMysql->query($search);
        $searchInfo = $valid->fetchAll();
        if ($searchInfo[0]['total'] > 0) {
            $deteSql = "DELETE FROM `hr_attendance_sum` WHERE `attendance_month` = " . $total['000001']['month'] . " and `attendance_year` = " . $total['000001']['year'];
            $this->dbMysql->exec($deteSql);
            echo "该月数据已经存过了!正在进行重新存储！", "<br />";
            return $this->inputAttendance($total);
        } else {
            try {
                $this->dbMysql->beginTransaction();
                $cunrrentTime = date('Y-m-d H:i:s', time());
                foreach ($total as $val) {
                    $sql = "insert into hr_attendance_sum(`emp_sn`,`attendance_info`,`total_count`,`usefor_total`,`error_count`,`attendance_year`,`attendance_month`,`createtime`,`modifytime`)
                            values (:sn, :info, :total,:useforTotal, :error, :year, :month,:createtime,:modifytime )";
                    //使用此sql语句 :user :password 绑定参数
                    $stmt = $this->dbMysql->prepare($sql);
                    //为sql语句中的变量绑定变量
                    $stmt->bindParam(':sn', $val['empSn']);
                    $stmt->bindParam(':info', $val['item']);
                    $stmt->bindParam(':total', $val['totalCount']);
                    $stmt->bindParam(':useforTotal', $val['useforDays']);
                    $stmt->bindParam(':error', $val['errorCount']);
                    $stmt->bindParam(':year', $val['year']);
                    $stmt->bindParam(':month', $val['month']);
                    $stmt->bindParam(':createtime', $cunrrentTime);
                    $stmt->bindParam(':modifytime', $cunrrentTime);
                    $stmt->execute();
                }
                if ($this->dbMysql->commit()) {
                    $result['message'] = "写入hr_attendance_sum成功";
                    $result['status'] = true;
                    echo "写入hr_attendance_sum成功!", '<br />';

                    $this->afterSave($total);
                    return $result;
                } else {
                    $result['message'] = "写入hr_attendance_sum出错了";
                    $result['status'] = false;
                    echo "写入hr_attendance_sum出错了!", '<br />';
                    return $result;
                }

            } catch (PDOexception $e) {
                $this->dbMysql->rollback();
                echo $e->getCode() . '-----' . $e->getMessage();
                $this->dbMysql = null;
            }
        }

    }

    private function getId($type, $sn)
    {
        if ($type == 'user') {
            $sql = "SELECT user_id FROM plt_user WHERE user_sn = " . $sn;
            $user = $this->dbMysql->query($sql);
            $id = $user->fetchColumn();
        } else {
            $sql1 = "SELECT emp_id,emp_dept FROM hr_employee WHERE emp_sn = " . $sn;
            $emps = $this->dbMysql->query($sql1);
            $id = $emps->fetchAll();
        }
        return $id;
    }

    private function afterSave($total)
    {
        foreach ($total as $val) {
            $emp = $this->getId('emp', $val['empSn']);
            $userId = $this->getId('user', $val['empSn']);

            $data['emp_id'] = $emp[0]['emp_id'];
            $data['dept_id'] = $emp[0]['emp_dept'];
            $data['atd_year'] = $val['year'];
            $data['atd_month'] = $val['month'];
            $data['createtime'] = date('Y-m-d H:i:s', time());
            $data['modifytime'] = date('Y-m-d H:i:s', time());
            $data['emp_sn'] = $val['empSn'];

            //出差天数计算
            $vacation = 0.0;
            $vacaArray = $this->getVacationDays($userId, $val);
            if ($vacaArray) {
                foreach ($vacaArray as $key => $vaca) {

                    $vacation += $vaca['vacadays'];

                }
            }
            $data['dept_travel_days'] = $vacation;

            //加班天数计算
            $deptWorkday = 0; //初始化数据
            $deptWorkArray = $this->getLeaveDays($userId, $val);
            if ($deptWorkArray) {
                foreach ($deptWorkArray as $key => $v) {
                    $deptWorkday += $v['days'];
                }
            }
            $data['dept_works_days'] = $deptWorkday;

            //休假天数
            $deptRecordDays = 0;
            $deptRecordArray = $this->getRecordDays($userId, $val);
            if ($deptRecordArray) {
                foreach ($deptRecordArray as $key => $v) {
                    $deptRecordDays += $v['days'];
                }
            }
            if (!$deptRecordDays) $deptRecordDays = '0.0';
            $data['dept_leavel_days'] = $deptRecordDays;

            $data['clock_days'] = $val['totalCount']; //总打卡天数
            $data['usefor_clock_days'] = $val['useforDays']; //有效打卡天数
            $data['abnormal_days'] = $val['errorCount']; //异常打卡天数

            //用户实际出勤天数
            $deptdays = $this->getUserAAtdDays($val['useforDays'], $deptWorkArray, $vacaArray, $deptRecordArray, $val['year'], $val['month'], $val['empSn']);
            $data['a_atd_days'] = $deptdays;

            //获取加班总天数
            $satds = $this->getSAtdDaysByEmpid($emp[0]['emp_id'], $val['year'], $val['month']);
            $data['o_work_days'] = $deptdays - $satds;
            if ($this->makeSureIsDeal($emp[0]['emp_id'], $val['year'], $val['month'])) {
                $this->setApplyByUserId($data, true);
            } else {
                $this->setApplyByUserId($data, false);
            }

        }
        echo "写入hr_attendance_check成功!";
        return true;
    }

    /**
     * 是否已有数据
     * @param $empId
     * @param $year
     * @param $month
     * @return bool
     */
    private function makeSureIsDeal($empId, $year, $month)
    {
        $sql = "SELECT * FROM hr_attendence_check WHERE emp_id = $empId AND atd_year =  $year and atd_month = $month";
        $info = $this->dbMysql->query($sql);
        $res = $info->fetchAll();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 存储考勤总表
     * @param $data
     * @param $isHave
     * @return mixed
     */
    private function setApplyByUserId($data, $isHave)
    {
        if ($data) {
            if ($isHave) {
                $sql = " update hr_attendence_check set ";
                $str = '';
                foreach ($data as $key => $val) {
                    $str .= " ,$key = '$val'";
                }
                $sql .= substr($str, 2);
                $sql .= " where emp_id = " . $data['emp_id'] . " and atd_year =  " . $data['atd_year'] . " and  atd_month = " . $data['atd_month'];
                return $this->dbMysql->query($sql);
            } else {
                $sql = "insert into hr_attendence_check(`emp_id`,`dept_id`,`atd_year`,`atd_month`,`emp_sn`,`dept_travel_days`,`dept_works_days`,`dept_leavel_days`,`clock_days`,`usefor_clock_days`,`abnormal_days`,`a_atd_days`,`o_work_days`,`createtime`,`modifytime`)
                values (" . $data['emp_id'] . ", " . $data['dept_id'] . ", " . $data['atd_year'] . ", " . $data['atd_month'] . "," . $data['emp_sn'] . ", " . $data['dept_travel_days'] . ", " . $data['dept_works_days'] . ", " . $data['dept_leavel_days'] . " , " . $data['clock_days'] . " , " . $data['usefor_clock_days'] . ",
                 " . $data['abnormal_days'] . ", " . $data['a_atd_days'] . ", " . $data['o_work_days'] . ", '" . $data['createtime'] . "', '" . $data['modifytime'] . "')";
                return $this->dbMysql->query($sql);
            }

        }
    }

    /**
     * 加班总天数
     * @param $empId
     * @param $year
     * @param $month
     * @return string
     */
    private function getSAtdDaysByEmpid($empId, $year, $month)
    {
        $sql = "SELECT * FROM hr_attendence_check WHERE emp_id = $empId AND atd_year = $year AND atd_month = $month ";
        $result = $this->dbMysql->query($sql);
        $days = $result->fetchAll();
        $satds = '';
        if ($days) {
            foreach ($days as $val) {
                $satds = $val['s_atd_days'];
            }
        }
        return $satds;
    }

    /**
     * 获取用户实际出勤天数。（打卡天数，加班天数，出差天数的综合）
     * @param $clockDays
     * @param $worksArray
     * @param $vacaArray
     * @param $deptLeavelArray
     * @param $year
     * @param $month
     * @param string $usersn
     * @return int
     */
    private function getUserAAtdDays($clockDays, $worksArray, $vacaArray, $deptLeavelArray, $year, $month, $usersn = '')
    {

        $deptWorkday = 0;
        $workdays = 0;
        //获取用户打卡数组

        if ($usersn) {
            $sql = "SELECT * FROM hr_attendance_sum WHERE emp_sn = $usersn";
            $total = $this->dbMysql->query($sql);
            $clockArr = $total->fetchAll();

            if ($clockArr) {
                foreach ($clockArr as $key => $val) {
                    $clockinfo = unserialize($val['attendance_info']);
                }
            }
        }
        //判断加班时间和打卡时间交集情况
        if ($worksArray) {
            $harfday = array();
            foreach ($clockinfo as $k => $v) {
                $clickdayArr[$k]['day'] = $v['recdate'];
                $clickdayArr[$k]['starttime'] = strtotime("$v[recdate]. $v[starttime]");
                $clickdayArr[$k]['endtime'] = strtotime("$v[recdate]. $v[endtime]");
                $clickdayArr[$k]['kaishi'] = $v['starttime'];
                $clickdayArr[$k]['jiesu'] = $v['endtime'];
                $a = $v['endtime'] - $v['starttime'];
                if ($a > 3 && $a < 7) {
                    $harfday[] = $v['recdate'];
                } else {
                    $datetime[] = $v['recdate'];
                }

            }

            foreach ($worksArray as $key => $val) {

                $workstarttime = strtotime("$val[start_date].$val[start_time]");
                $workendtime = strtotime("$val[end_date].$val[end_time]");

                //判断申请加班，但是没有打卡的信息
                if (!in_array($val['start_date'], $datetime) && !in_array($val['end_date'], $datetime)) {
                    $workdays += $val['days'];

                } else {
                    if ($harfday) {
                        if (in_array($val['start_date'], $harfday)) {
                            $workdays += 0.5;
                        }
                    }
                    foreach ($clickdayArr as $k => $v) {
                        //判断小于一天的加班申请情况
                        if ($val['days'] <= 1) {
                            if ($v['day'] == $val['start_date']) {

                                //排除在正常上班时间（9-18点之外的加班打卡）
                                if (($v['starttime'] < $workstarttime && $v['endtime'] < $workstarttime) || $v['starttime'] > $workendtime && $v['endtime'] < $workendtime) {

                                    $workdays += $val['days'];
                                }
                            }
                        }
                    }
                }
            }
        }

        //判断打卡天数和出差天数冲突
        if ($clockinfo) {
            if ($vacaArray) {
                foreach ($clockinfo as $k => $err) {
                    foreach ($vacaArray as $key => $item) {

                        $vTimeFrom = $item['time_from'];
                        $vTimeTo = $item['time_to'];
                        if ($vTimeFrom <= $err['recdate'] && $err['recdate'] <= $vTimeTo) {
                            $b = $err['endtime'] - $err['starttime'];
                            if ($b > 3 && $b < 7) {
                                $clockDays = $clockDays - 0.5;
                            } else if ($b > 7) {
                                $clockDays = $clockDays - 1;
                            }
                        }

                    }
                }
            }

        }

        //获取休假内容
        //用户休假类型为 事假（01），病假（02），工伤假（11）不享受薪资
        $leavedays = 0;
        if ($deptLeavelArray) {
            foreach ($deptLeavelArray as $key => $val) {
                if ($val['type_sn'] != 01 && $val['type_sn'] != 02 && $val['type_sn'] != 11) {
                    $leavedays += $val['days'];
                }
            }
        }

        //判断出差时间和加班时间是否有交集
        $days = 0;
        if ($vacaArray) {
            foreach ($vacaArray as $vaca) {
                $days += $vaca['vacadays'];
            }
        }
        $hreftime = 0;
        $hrefday = 0;
        if ($vacaArray) {
            if ($worksArray) {
                foreach ($worksArray as $k => $v) {
                    $daysArr = explode('.', $v['days']);
                    if ($daysArr[1]) {
                        $hreftime = $v['start_date'];
                        foreach ($vacaArray as $key => $val) {
                            if ($val["time_from"] <= $hreftime && $hreftime <= $val['time_to']) {
                                $hrefday += "0.$daysArr[1]";
                            }

                        }
                    }
                }
            }
        }
        //出勤天数 = 打卡天数 + 加班天数 + 出差天数  + 休假天数
        $daysss = $clockDays + $days - $hrefday + $workdays + $leavedays;

        return $daysss;
    }

    /**
     * 获取休假天数
     * @param $userId
     * @param $val
     * @return int
     */
    private function getRecordDays($userId, $val)
    {
        if (is_int($val['month'])) {
            if ($val['month'] < 10) {
                $val['month'] = '0' . $val['month'];
            }
        }
        $time = $val['year'] . "-" . $val['month'];
        $sql = "SELECT * FROM hr_leave_record WHERE 1=1  ";
        $sql .= " and uid = $userId and start_date like '%$time%'  and end_date like '%$time%' ";
        $leaveRecords = $this->dbMysql->query($sql);
        $deptRecordArray = $leaveRecords->fetchAll();
        return $deptRecordArray;
    }

    /**
     * 获取加班天数
     * @param $userId
     * @param $val
     * @return int
     */
    private function getLeaveDays($userId, $val)
    {
        if (is_int($val['month'])) {
            if ($val['month'] < 10) {
                $val['month'] = '0' . $val['month'];
            }
        }
        $time = $val['year'] . "-" . $val['month'];

        $sql = "SELECT * FROM hr_leave_days where 1=1  ";
        $sql .= " and uid = $userId and start_date like '%$time%'  and end_date like '%$time%' ";
        $leaveDays = $this->dbMysql->query($sql);
        $deptWorkArray = $leaveDays->fetchAll();
        return $deptWorkArray;
    }

    /**
     * 出差天数计算
     * @param $userId
     * @param $val
     * @return array
     */
    private function getVacationDays($userId, $val)
    {
        $vacaday = array();
        $sql2 = " SELECT * FROM  oa_sys a join oa_vacation b on  a.id  = b.sys_id  ";
        $sql2 .= " WHERE a.user_id = $userId  AND  a.flow_id = 2  AND  a.current_status = '05'";
        $user = $this->dbMysql->query($sql2);
        $result = $user->fetchAll();
        if (is_int($val['month'])) {
            if ($val['month'] < 10) {
                $val['month'] = '0' . $val['month'];
            }
        }
        $firstday = $val['year'] . "-" . $val['month'] . "-01";
        $lastday = date('Y-m-t', strtotime("01." . $val['month'] . "." . $val['year'])); //取得每个月的最后一天
        //遍历出差记录得到当月出差数据
        if ($result) {
            foreach ($result as $key => $item) {
                $travel = unserialize($item['travel_all']);
                $result[$key]['travel'] = $travel;
                foreach ($travel as $k => $v) {
                    //判断符合条件的申请情况  组合 出差申请的数组
                    if ($v['time_from'] >= $firstday && $v['time_to'] <= $lastday) {
                        $vacaday[$key][$k]['time_from'] = $v['time_from'];
                        $vacaday[$key][$k]['time_to'] = $v['time_to'];
                        $vacaday[$key][$k]['user_id'] = $item['user_id'];
                        $vacaday[$key][$k]['dept_id'] = $item['dept_id'];
                        $vacaday[$key][$k]['flow_id'] = $item['flow_id'];
                        $vacaday[$key][$k]['flow_user'] = $item['flow_user'];

                    } elseif ($v['time_from'] >= $firstday && $v['time_from'] <= $lastday && $v['time_to'] > $lastday) {
                        $vacaday[$key][$k]['time_from'] = $v['time_from'];
                        $vacaday[$key][$k]['time_to'] = $lastday;
                        $vacaday[$key][$k]['user_id'] = $item['user_id'];
                        $vacaday[$key][$k]['dept_id'] = $item['dept_id'];
                        $vacaday[$key][$k]['flow_id'] = $item['flow_id'];
                        $vacaday[$key][$k]['flow_user'] = $item['flow_user'];

                    } elseif ($v['time_from'] < $firstday && $v['time_to'] <= $lastday && $v['time_to'] >= $firstday) {
                        foreach ($travel as $kk => $vv) {
                            if ($vv['time_to'] > $v['time_to'] && $vv['time_from'] <= $firstday) {

                                $vacaday[$key][$k]['time_from'] = $firstday;
                                $vacaday[$key][$k]['time_to'] = $vv['time_to'];
                                $vacaday[$key][$k]['user_id'] = $item['user_id'];
                                $vacaday[$key][$k]['dept_id'] = $item['dept_id'];
                                $vacaday[$key][$k]['flow_id'] = $item['flow_id'];
                                $vacaday[$key][$k]['flow_user'] = $item['flow_user'];

                            } else {
                                unset($travel[$k]);
                            }
                        }

                    } elseif ($v['time_from'] < $firstday && $v['time_to'] > $lastday) {
                        $vacaday[$key][$k]['time_from'] = $v['time_from'];
                        $vacaday[$key][$k]['time_to'] = $v['time_to'];
                        $vacaday[$key][$k]['user_id'] = $item['user_id'];
                        $vacaday[$key][$k]['dept_id'] = $item['dept_id'];
                        $vacaday[$key][$k]['flow_id'] = $item['flow_id'];
                        $vacaday[$key][$k]['flow_user'] = $item['flow_user'];
                    }
                }
            }
        }
        if (!$vacaday) {
            return false;
        }
        $newArr = array();
        foreach ($vacaday as $key => $item) {
            foreach ($item as $kkk => $v) {
                array_push($newArr, $v);
            }
        }

        $filterArr = self::test($newArr);
        foreach ($filterArr as $a => $b) {
            $filterArr[$a]['vacadays'] = round((strtotime($b['time_to']) - strtotime($b['time_from'])) / 86400) + 1;
        }
        return $filterArr ? $filterArr : false;
    }

    static function test($newArr)
    {
        $length = count($newArr);
        $filterArr = array();
        foreach ($newArr as $v) {
            $isExist = false;
            $vx = strtotime($v['time_from']);
            $vy = strtotime($v['time_to']);
            foreach ($filterArr as $key2 => $val) {
                $vax = strtotime($val['time_from']);
                $vay = strtotime($val['time_to']);
                if ($vax >= $vx && $vay <= $vy) {
                    $filterArr[$key2]['time_from'] = $v['time_from'];
                    $filterArr[$key2]['time_to'] = $v['time_to'];

                    $isExist = true;
                    break;
                } elseif ($vax <= $vx && $vay >= $vy) {
                    $isExist = true;
                    break;
                } elseif ($vax <= $vy && $vy <= $vay) {
                    $filterArr[$key2]['time_from'] = $v['time_from'];
                    $isExist = true;
                    break;
                } elseif ($vy >= $vay && $vay >= $vx) {
                    $filterArr[$key2]['time_to'] = $v['time_to'];
                    $isExist = true;
                    break;
                }
            }
            if (!$isExist) {
                array_push($filterArr, $v);
            }
        }
        if (count($filterArr) == $length) {
            return $filterArr;
        } else {
            return self::test($filterArr);
        }
    }

    /**
     * 总入口
     */
    public function index()
    {
        $deptIds = $this->getDepts();
        $empSn = $this->getEmps($deptIds);
        $forwardMonth = $this->getForwardMoth();
        $result = $this->getWorkAttendance($empSn, $forwardMonth);
        $total = $this->getTotal($result); //获取员工的打卡信息
        $result = $this->inputAttendance($total);

        $sum = "select count(*) as total from hr_attendance_sum where `attendance_year` = " . date("Y") . " and `attendance_month` = " . date("m", strtotime("-1 month"));
        $validSum = $this->dbMysql->query($sum);
        $totalSum = $validSum->fetchAll();

        $check = "select count(*) as total from hr_attendence_check where `atd_year` = " . date("Y") . " and `atd_month` = " . date("m", strtotime("-1 month")) . " and `dept_id` in " . $deptIds;
        $validCheck = $this->dbMysql->query($check);
        $totalCheck = $validCheck->fetchAll();

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
//        $end = $sm->SendMail($sendTo,'itadmin@7sef.com',$title,$content);
        if ($end) echo $end;
        else echo "发送成功！";
    }

}
//实例化考勤类
$attendance = new Attendance();
//$attendance->index();

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