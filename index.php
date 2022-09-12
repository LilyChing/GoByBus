<?php
define('DB_HOST', 'sql6.freesqldatabase.com');
define('DB_NAME', 'sql6518937');
define('DB_USER', 'sql6518937');
define('DB_PASSWORD', 'H5lyRxTcju');
$dbconnection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 3306);
if ($dbconnection->connect_errno != 0) {
    echo $dbconnection->connect_error;
    exit();
}
$json_data = file_get_contents("nation_2022-08-17.json");
$dataset = json_decode($json_data, JSON_OBJECT_AS_ARRAY);
$res = mysqli_query($dbconnection, "SELECT DISTINCT `date` FROM `casesInUK` ORDER BY `date`");
                if (mysqli_num_rows($res) > 0) {
                    while ($data = mysqli_fetch_assoc($res)) {
                        $date = $data['date'];
                        $totalCasesQ = mysqli_query($dbconnection, "SELECT SUM(`hospitalCases`) as total_cases FROM `casesInUK` WHERE DATE(`date`) = DATE('" . $date . "')");
                        $totalCases = mysqli_fetch_assoc($totalCasesQ);
                        $rows[] = array($date,$totalCases['total_cases']);
                    }
                }
?>
<!DOCTYPE html>
<html>
<head>
    <title>GoByBusTest</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        //google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            console.log(<?php echo json_encode($rows, JSON_NUMERIC_CHECK); ?>);
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', 'Daily cases');
            data.addRows(
                <?php echo json_encode($rows, JSON_NUMERIC_CHECK); ?>
            );
            var options = {
                title: 'Daily hospital Cases in UK',
                curveType: 'function',
                legend: {
                    position: 'bottom'
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            chart.draw(data, options);
        }
    </script>
</head>
<body>
    <form method="post">
        <input type="submit" name="import" id="import" value="import Data" /><br />
    </form>
    <div id="curve_chart" style="width: 900px; height: 500px"></div>
    <?php
    function importData()
    {
        global $dbconnection, $dataset;
        $stmt = $dbconnection->prepare("
        INSERT INTO casesInUK(areaType,areaCode,areaName,date,hospitalCases) VALUES(?,?,?,?,?)
        ");
        if ($stmt) {
        $stmt->bind_param("ssssi", $areaType, $areaCode, $areaName, $date, $hospitalCases);
        }
        foreach ($dataset['body'] as $case) {
            $areaType = $case['areaType'];
            $areaCode = $case['areaCode'];
            $areaName = $case['areaName'];
            $date = $case['date'];
            $hospitalCases = $case['hospitalCases'];
            if ($stmt) {
            $stmt->execute();
            }
        }
        echo "Done. Here is the data chart. <br/>";
        echo "<script> google.charts.setOnLoadCallback(drawChart);</script>";
    }
    if (array_key_exists('import', $_POST)) {
        importData();
    }
    ?>
</body>
</html>