<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);
set_time_limit(0);
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.nativerank.com
 * @since      1.0.0
 *
 * @package    Nr_Bpi
 * @subpackage Nr_Bpi/admin/partials
 */
?>
    <style>
        .drop {
            width: 96%;
            height: 96%;
            border: 3px dashed #DADFE3;
            border-radius: 15px;
            overflow: hidden;
            text-align: center;
            background: white;
            -webkit-transition: all 0.5s ease-out;
            -moz-transition: all 0.5s ease-out;
            transition: all 0.5s ease-out;
            margin: auto;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            /*&:hover
             * cursor: pointer
             * background: #f5f5f5 */
        }
        .drop .cont {
            width: 500px;
            height: 170px;
            color: #8E99A5;
            -webkit-transition: all 0.5s ease-out;
            -moz-transition: all 0.5s ease-out;
            transition: all 0.5s ease-out;
            margin: auto;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }
        /*.drop .cont i {*/
            /*font-size: 400%;*/
            /*color: #8E99A5;*/
            /*position: relative;*/
        /*}*/
        .drop .cont .tit {
            font-size: 400%;
            line-height: 1;
            text-transform: uppercase;
        }
        .drop .cont .desc {
            color: #A4AEBB;
        }
        .drop .cont .browse1 {
            margin: 10px 25%;

        }
        .drop input {
            width: 100%;
            height: 100%;
            cursor: pointer;
            background: red;
            opacity: 0;
            margin: auto;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }

        #list {
            width: 100%;
            text-align: left;
            position: absolute;
            left: 0;
            top: 0;
        }
        #list .thumb {
            height: 75px;
            border: 1px solid #323a44;
            margin: 10px 5px 0 0;
        }

        #result{
            background:#f9f9f9;
            padding:20px;
        }
        pre{
            padding:20px;
            background:#fff;
        }

.uploader{
    position: relative;
    height:500px;
    margin-top:40px;
}

        #startImport{
            position: absolute;
            bottom:0;
            left:50%;
            transform:translate(-50%, 0);
            z-index: 999;

        }
    </style>
<div class="uploader">
    <div class="drop">
        <div class="cont">
            <i class="fa fa-cloud-upload"></i>
            <div class="tit">
                Drag & Drop
            </div>
            <div class="desc">
                your csv file
            </div>
            <div class="browse1 btn">
                click here to browse
            </div>
            <div id="file-container" style="display:none">
                <div class="browse2 btn-large disabled"><i class="material-icons left">
                    insert_drive_file
                </i> <span id="file_name"></span>
                </div>
            </div>

        </div>
        <form action="#" method='POST' enctype='multipart/form-data' id="importFile">
        <output id="list"></output><input id="files"  name="csvFile" type="file" accept=".csv"/>
        </form>
        <buton id="startImport" style="display: none;" class="btn waves-effect waves-light red darken-4">Import <i class="material-icons right">send</i></buton>
    </div>

</div>
    <script>
        (function( $ ) {
            'use strict';

            var drop = $("input");
            drop.on('dragenter', function (e) {
                $(".drop").css({
                    "border": "4px dashed #09f",
                    "background": "rgba(0, 153, 255, .05)"
                });
                $(".cont").css({
                    "color": "#09f"
                });
            }).on('dragleave dragend mouseout drop', function (e) {
                $(".drop").css({
                    "border": "3px dashed #DADFE3",
                    "background": "transparent"
                });
                $(".cont").css({
                    "color": "#8E99A5"
                });
            });



            function handleFileSelect(evt) {
                console.log(evt);
                var files = evt.target.files; // FileList object

                $('#file_name').text(files[0].name);
                $('#file_name').after("<p>Last Modified : "+timeConverter(files[0].lastModified)+"</p>");
                $('#file_name').after("<p>"+bytesToSize(files[0].size)+"</p>");

                $('#file-container').show();
                $('#startImport').show();
            }
function timeConverter(UNIX_timestamp){
  var a = new Date(UNIX_timestamp * 1000);
  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
  return time;
}

function bytesToSize(bytes) {
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
  if (bytes === 0) return 'n/a'
  const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
  if (i === 0) return `${bytes} ${sizes[i]}`
  return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`
}

            $('#files').change(handleFileSelect);

            $('#startImport').click(function(){
                $('#importFile').submit();
            });

        })( jQuery );

    </script>
    <div class="mycontainer">

        <div class="progress">
            <div class="determinate" id="nrbpi-loader" style="width: 0%"></div>
        </div>
        <div align="center"><span id="pending">0</span> of <span id="total">0</span> files updated</div>
       
       <br><br> <div align="left" id="result">Log:<hr></div>
    </div>

    <script>
        var filesRecieved = 0;
        function sendData(filename, x) {
            jQuery(document).ready(function ($) {
                var file = filename;
                filesRecieved++;

                var perc = (filesRecieved / csvFiles.length ) * 50;
                $('#nrbpi-loader').css('width', perc + '%');

                $.ajax({
                    type: "POST",
                    url: "<?php echo get_site_url(); ?>/wp-admin/edit.php?page=csv_importer_api",
                    data: {file},
                    async: true,
                    success: function (html) {

                        var perc = (filesRecieved / csvFiles.length ) * 100;
                        $('#nrbpi-loader').css('width', perc + '%');
                        var str = html;
                        var stringnum = str.indexOf("{csv_result}");
                        var res = str.substring(stringnum);
                        if (res.indexOf('csv_result') != -1) {
                            $('#result').append("<div>"+res.substring(res.lastIndexOf("{csv_result}")+12)+"</div>");
                            $('#pending').text(filesRecieved );

                        } else
                        {
                            console.log(html);
                        }
                        if((x +1) < (csvFiles.length)) {
                            x++;
                            sendData(csvFiles[x], x);
                        } else {
                            $('#result').append("<u>Processed " + (x + 1) + " Files</u>");
                        }
                    }
                })
            });
        }
    </script>
<?php
if (isset($_FILES['csvFile'])) {
    echo "<style>.uploader{height:200px!important;}</style>";
    echo "<script>var csvFiles = [];</script>";
    $csv = array();
    $headers = array();
    $putHeaders = true;
    $batchsize = 1000; //split huge CSV file by 1,000
    if ($_FILES['csvFile']['error'] == 0) {
        $name = $_FILES['csvFile']['name'];
        $ext = explode('.', $_FILES['csvFile']['name']);
        $ext = strtolower(end($ext));
        $tmpName = $_FILES['csvFile']['tmp_name'];
        if ($ext === 'csv') { //check if uploaded file is of CSV format
            if (($handle = fopen($tmpName, 'r')) !== FALSE) {
                set_time_limit(0);
                $row = 0;

                while (($data = fgetcsv($handle)) !== FALSE) {
                    $jsonData = array();
                    $col_count = count($data);
                    //splitting of CSV file :
                    if ($row % $batchsize == 0):
                        $file_path = plugin_dir_path(__FILE__) . "temp/";
                        $file = fopen($file_path . "minpoints$row.csv", "w");

                        chmod($file_path . "minpoints$row.csv", 0766);


                    endif;

                    if ($row === 0) {
                        foreach ($data as $i => $key) {
                            $key = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $key);
                            array_push($headers, $data[$i]);


                        }
                        $headers = json_encode($headers);
                        $headers = str_replace("[", "", $headers);
                        $headers = str_replace("]", "", $headers);
                    } else {

                        foreach ($data as $i => $key) {
                            $key = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $key);
                            $csv[$row][$key] = $data[$i];
                            array_push($jsonData, $key);

                        }
                    }


                    $json = json_encode($jsonData);
                    $json = str_replace("[", "", $json);
                    $json = str_replace("]", "", $json);


                    if ($row == 0 || $row % $batchsize == 0) {
                        $finaljson = $headers . "\n" . $json;
                        $putHeaders = false;
                    }
                    else {


                        $finaljson = $json;
                    }
                        fwrite($file, $finaljson . PHP_EOL);
                        //sending the splitted CSV files, batch by batch...
                        if ($row % $batchsize == 0):
                            echo "<script> csvFiles.push('" . $file_path . "minpoints$row.csv'); </script>";
                        endif;

                    $row++;
                }
                echo "<script>const totalFiles = $row; document.querySelector('#total').innerHTML =(csvFiles.length);</script>";
                fclose($file);
                fclose($handle);
            }
        }
        echo "<script>
x = 0;
sendData(csvFiles[x], x);

</script>";
    } else {
        print_r( $_FILES['csvFile'] );
        echo "ERROR! Only CSV files are allowed.";
    }
}

?>
