<?php
namespace app\commands;

use app\models\Observe;
use app\models\User;
use yii\console\Controller;

class SeedController extends Controller
{
    public function actionIndex()
    {
        /*
         * 0 => Objektum (kész)
         * 1 => NGC (kész)
         * 2 => Csillagkép (kész)
         * 3 => Típus (GH, NYH, stb) (kész)
         * 4 => Távcső
         * 5 => Seeing (nyugodtság) (kész)
         * 6 => Transparency (átlátszóság) (kész)
         * 7 => Hely (kész)
         * 8 => Időpont
         * 9 => Forrás (kész)
         * 10 => Észlelő (kész)
         * 11 => Leírás
         */
        $path = __DIR__ . '/../seed/megfigyelesek.csv';
        $row = 0;
        if (($handle = fopen($path, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $row++;
                if ($row == 1) {
                    continue;
                }

                $object = new Observe();
                $object->type = Observe::TYPE_DEEP_SKY;
                $object->object_name = $this->getName($data);
                $object->constellation = $data[2];
                $object->object_type = $data[3];
                $object->telescope = $data[4];
                $object->seeing = (int) self::purgeSeeing($data[5]);
                $object->transparency = (int) self::purgeSeeing($data[6]);
                $object->location = $data[7];
                $object->date = self::processDate($data[8]);
                $object->source = $data[9];
                $object->description = $data[11];

                $users = explode(',', $data[10]);

                $user = User::find()->where(['name' => $users[0]])->one();
                if ($user == null) {
                    $user = new User();
                    $user->name = $users[0];
                    $user->email = "noemail@email.hu";
                    $user->save(false);
                    $user->refresh();
                }

                $object->observer_id = $user->id;


                echo "$row:\n";
                try {
                    $object->save();
                    echo $object->id;
                    echo "\n";
                } catch (\Exception $e) {
                    var_dump($e);
                }

            }
            fclose($handle);
        }
    }

    private static function processDate($string) {
        $date = strtotime($string);
        if ($date !== false) {
            return date('Y-m-d', $date);
        }

        $date = self::translateMonth($string);

        $explodedDate = explode(" ", $date);
        if (isset($explodedDate[2])) {
            $temp[] = $explodedDate[0];
            $temp[] = $explodedDate[1];
            $temp[] = explode("/", $explodedDate[2])[0];
            $date = implode("", $temp);
            $date = trim($date, " \n\r\t\v\0.");
            $date = str_replace(".", "-", $date);
        }

        return date('Y-m-d', strtotime($date));
    }

    /**
     *
     * @param $data
     * @return mixed|string
     */
    private function getName($data)
    {
        $name = $data[0];

        if (empty($data[0])) {
            $name = $data[1];

            $name = self::ngcPrefix($name);

            return $name;
        }

        if (empty($data[1])) {
            return self::ngcPrefix($name);
        }

        if (!empty($data[1]) && !empty($data[0]) && $data[0] !== $data[1]) {
            $name .= " (" . self::ngcPrefix($data[1]) . ")";
        }

        return self::ngcPrefix($name);
    }

    /**
     * If no cataloge is there, then add NGC prefix before the number
     * @param $string
     * @return mixed|string
     */
    private static function ngcPrefix($string) {
        if (!preg_match('([a-zA-Z])', $string)) {
            $string = "NGC " . $string;
        }

        return $string;
    }

    /**
     * If a range is given, convert it to a single int.
     * @param $seeing
     * @return mixed|string
     */
    private static function purgeSeeing($seeing)
    {
        $result = explode("-", $seeing);

        return $result[0];
    }


    private static function translateMonth($string) {
        foreach (self::monthsAbbr() as $index => $month) {
            $count = 0;
            $res = str_replace($month, $index. ".", $string, $count);

            if ($count > 0) {
                return $res;
            }
        }

        foreach (self::monthsFull() as $index => $month) {
            $count = 0;
            $res = str_replace($month, $index . ".", $string, $count);

            if ($count > 0) {
                return $res;
            }
        }
        return $string;
    }

    private static function monthsAbbr()
    {
        $result = [
            '01' => 'jan.',
            '02' => 'feb.',
            '03' => 'márc.',
            '04' => 'ápr.',
            '05' => 'máj.',
            '06' => 'jún.',
            '07' => 'júl.',
            '08' => 'aug.',
            '09' => 'szept.',
            '10' => 'okt.',
            '11' => 'nov.',
            '12' => 'dec.',
        ];
        return $result;
    }

    private static function monthsFull()
    {
        $result = [
            '01' => 'január',
            '02' => 'február',
            '03' => 'március',
            '04' => 'április',
            '05' => 'május',
            '06' => 'június',
            '07' => 'július',
            '08' => 'augusztus',
            '09' => 'szeptepmber',
            '10' => 'október',
            '11' => 'november',
            '12' => 'december',
        ];
        return $result;
    }

}