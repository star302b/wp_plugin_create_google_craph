<script type="text/javascript">
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string','Order data');
        <?php
        if(get_option('wp_create_graph_connection')==='remote'){
            $wpdb = new wpdb(get_option('wp_create_graph_user'),get_option('wp_create_graph_password'),get_option('wp_create_graph_database'),get_option('wp_create_graph_host'));
        }
        else {
            global $wpdb;
        }
        $arr_super = [];
        $array_gr = [];
        ksort($array_finish);
        foreach ($array_finish as $key => $value){
            $result = $wpdb->get_results("SELECT pg_nameshort FROM productgroups WHERE pg_id=".$key."");
            echo "data.addColumn('number','".$result[0]->pg_nameshort."');";
            if(!in_array($key,$array_gr)){
                $array_gr[] = $key;
            }
            foreach ($value as $key_n =>$value_n){
                $arr_super[$key_n][$key] = $value_n;
            }
        }
        ksort($arr_super);
        ?>
        data.addRows([
          <?php foreach ($arr_super as $key=>$value){
              echo "['$key',";
              for($i=0;$i<count($array_gr);$i++){
                  if(isset($value[$array_gr[$i]])){
                      echo $value[$array_gr[$i]].',';
                  }
                  else
                      echo "null,";
              }
              echo "],";
        } ?>
        ]);
        var options = {
            width: 600,
            height: 400,
            legend: { position: 'top', maxLines: 3 },
            bar: { groupWidth: '75%' },
            isStacked: true
        };
        var chart = new google.visualization.BarChart(document.getElementById("barchart_values"));
        chart.draw(data, options);
    }
</script>
<div style="text-align: center">
<h2>Product Chart</h2>
<div id="barchart_values" style="width: 900px; height: 300px;"></div>
</div>