<?php 
require_once('db.php');

\header('Access-Control-Allow-Origin: *');
\header('Content-Type: application/json; charset=utf-8'); 
 echo "<h3> LANTHAN Database </h3>";

$pUser = $_GET['user'] ?? null;
if (\is_null($pUser) || $pUser === '') {
   header('HTTP/1.0 403 Forbidden');
   echo \json_encode(['e' => 'invalid ID given']);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon">
  <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
  <META HTTP-EQUIV=Refresh CONTENT="10"/>
  <link href="css/status.css" rel="stylesheet" type="text/css" />
  <link href="css/status2.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript">
</script> 
</head>
<body>

<div class="col-sm-8">
		<div class="well clearfix">
			<div class="pull-right"><button type="button" class="btn btn-xs btn-primary" id="command-add" data-row-id="0">
			<span class="glyphicon glyphicon-plus"></span> Record</button></div></div>
		<table id="employee_grid" class="table table-condensed table-hover table-striped" width="60%" cellspacing="0" data-toggle="bootgrid">
			<thead>
				<tr>
					<th data-column-id="id" data-type="numeric" data-identifier="true">NR</th>
					<th data-column-id="employee_name">User ID</th>
					<th data-column-id="employee_salary">Active</th>
          <th data-column-id="employee_age">Error</th>
          <th data-column-id="employee_age">Timeout</th>
					<th data-column-id="commands" data-formatter="commands" data-sortable="false">Commands</th>
				</tr>
			</thead>
		</table>
    </div>

    <?php
    var grid = $("#employee_grid").bootgrid({
    ajax: true,
    rowSelect: true,
    post: function ()
    {
      /* To accumulate custom parameter with the request object */
      return {
        id: "b0df282a-0d67-40e5-8558-c9e93b7befed"
      };
    },
    
    url: "response.php",
    formatters: {
            "commands": function(column, row)
            {
                return "<button type=\"button\" class=\"btn btn-xs btn-default command-edit\" data-row-id=\"" + row.id + "\"><span class=\"glyphicon glyphicon-edit\"></span></button> " + 
                    "<button type=\"button\" class=\"btn btn-xs btn-default command-delete\" data-row-id=\"" + row.id + "\"><span class=\"glyphicon glyphicon-trash\"></span></button>";
            }
        }
   })
   ?>
   <?php
   include("connection.php");
	$db = new dbObj();
	$connString =  $db->getConnstring();

	$params = $_REQUEST;
	
	$action = isset($params['action']) != '' ? $params['action'] : '';
	$empCls = new Employee($connString);

	switch($action) {
	 default:
	 $empCls->getEmployees($params);
	 return;
	}
	class Employee {
	protected $conn;
	protected $data = array();
	function __construct($connString) {
		$this->conn = $connString;
	}
	
	public function getEmployees($params) {
    
    $this->data = $this->getRecords($params);
    
    echo json_encode($this->data);
  }
	
	function getRecords($params) {
    $rp = isset($params['rowCount']) ? $params['rowCount'] : 10;
    
    if (isset($params['current'])) { $page  = $params['current']; } else { $page=1; };  
        $start_from = ($page-1) * $rp;
    
    $sql = $sqlRec = $sqlTot = $where = '';
    
    if( !empty($params['searchPhrase']) ) {   
      $where .=" WHERE ";
      $where .=" ( employee_name LIKE '".$params['searchPhrase']."%' ";    
      $where .=" OR employee_salary LIKE '".$params['searchPhrase']."%' ";

      $where .=" OR employee_age LIKE '".$params['searchPhrase']."%' )";
     }
     
     // getting total number records without any search
    $sql = "SELECT * FROM `employee` ";
    $sqlTot .= $sql;
    $sqlRec .= $sql;
    
    //concatenate search sql if value exist
    if(isset($where) && $where != '') {

      $sqlTot .= $where;
      $sqlRec .= $where;
    }
    if ($rp!=-1)
    $sqlRec .= " LIMIT ". $start_from .",".$rp;
    
    
    $qtot = mysqli_query($this->conn, $sqlTot) or die("error to fetch tot employees data");
    $queryRecords = mysqli_query($this->conn, $sqlRec) or die("error to fetch employees data");
    
    while( $row = mysqli_fetch_assoc($queryRecords) ) { 
      $data[] = $row;
    }

    $json_data = array(
      "current"            => intval($params['current']), 
      "rowCount"            => 10,      
      "total"    => intval($qtot->num_rows),
      "rows"            => $data   // total data array
      );
    
    return $json_data;
  }
  ?>

<div id="add_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Employee</h4>
            </div>
            <div class="modal-body">
                <form method="post" id="frm_add">
				<input type="hidden" value="add" name="action" id="action">
                  <div class="form-group">
                    <label for="name" class="control-label">Name:</label>
                    <input type="text" class="form-control" id="name" name="name"/>
                  </div>
                  <div class="form-group">
                    <label for="salary" class="control-label">Salary:</label>
                    <input type="text" class="form-control" id="salary" name="salary"/>
                  </div>
				  <div class="form-group">
                    <label for="salary" class="control-label">Age:</label>
                    <input type="text" class="form-control" id="age" name="age"/>
                  </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btn_add" class="btn btn-primary">Save</button>
            </div>
			</form>
        </div>
    </div>
</div>

<?php

$( "#command-add" ).click(function() {
    $('#add_model').modal('show');
});

$( "#btn_add" ).click(function() {
  ajaxAction('add');
});


function ajaxAction(action) {
  data = $("#frm_"+action).serializeArray();
  $.ajax({
    type: "POST",  
    url: "response.php",  
    data: data,
    dataType: "json",       
    success: function(response)  
    {
    $('#'+action+'_model').modal('hide');
    $("#employee_grid").bootgrid('reload');
    }   
  });
}
case 'add':
  $empCls->insertEmployee($params);
break;

function insertEmployee($params) {
  $data = array();;
  $sql = "INSERT INTO `employee` (employee_name, employee_salary, employee_age) VALUES('" . $params["name"] . "', '" . $params["salary"] . "','" . $params["age"] . "');  ";
  
  echo $result = mysqli_query($this->conn, $sql) or die("error to insert employee data");
  
}


?>


<div id="edit_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Edit Employee</h4>
            </div>
            <div class="modal-body">
                <form method="post" id="frm_edit">
        <input type="hidden" value="edit" name="action" id="action">
        <input type="hidden" value="0" name="edit_id" id="edit_id">
                  <div class="form-group">
                    <label for="name" class="control-label">Name:</label>
                    <input type="text" class="form-control" id="edit_name" name="edit_name"/>
                  </div>
                  <div class="form-group">
                    <label for="salary" class="control-label">Salary:</label>
                    <input type="text" class="form-control" id="edit_salary" name="edit_salary"/>
                  </div>
          <div class="form-group">
                    <label for="salary" class="control-label">Age:</label>
                    <input type="text" class="form-control" id="edit_age" name="edit_age"/>
                  </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btn_edit" class="btn btn-primary">Save</button>
            </div>
      </form>
        </div>
    </div>
</div>

<?php

.on("loaded.rs.jquery.bootgrid", function()
{
    /* Executes after data is loaded and rendered */
    grid.find(".command-edit").on("click", function(e)
    {
        //alert("You pressed edit on row: " + $(this).data("row-id"));
      var ele =$(this).parent();
      var g_id = $(this).parent().siblings(':first').html();
            var g_name = $(this).parent().siblings(':nth-of-type(2)').html();
    
    $('#edit_model').modal('show');
          if($(this).data("row-id") >0) {
              
                                // collect the data
                                $('#edit_id').val(ele.siblings(':first').html()); // in case we're changing the key
                                $('#edit_name').val(ele.siblings(':nth-of-type(2)').html());
                                $('#edit_salary').val(ele.siblings(':nth-of-type(3)').html());
                                $('#edit_age').val(ele.siblings(':nth-of-type(4)').html());
          } else {
           alert('Now row selected! First select row, then click edit button');
          }
    })
})



$( "#btn_edit" ).click(function() {
  ajaxAction('edit');
});

case 'edit':
    $empCls->updateEmployee($params);
break;


function updateEmployee($params) {
  $data = array();
  //print_R($_POST);die;
  $sql = "Update `employee` set employee_name = '" . $params["edit_name"] . "', employee_salary='" . $params["edit_salary"]."', employee_age='" . $params["edit_age"] . "' WHERE id='".$_POST["edit_id"]."'";
  
  echo $result = mysqli_query($this->conn, $sql) or die("error to update employee data");
}

?>

















<!-- 

<script>
      $(document).ready(function(){
        $("#Filter1Input").on("keyup", function() {
          var value = $(this).val().toLowerCase();
          $("#Filter1Table tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
          });
        });
      });
    </script>  
    
    <div class="row">
      <div class="col">
        <h3 class="text-center"> <?php $datatext = "--.--.---- --:--:--"; echo date('d.m.Y H:i:s');?> </h3>
        <input class="form-control" id="Filter1Input" type="text" placeholder="Filter..">
      </div>
    </div> 
    
    <table class="table sortable table-responsive table-condensed table-hover">
      <thead>
        <tr>
          <th>userv ID</th>
          <th>device ID</th>
          <th>Active </th>
          <th>Error</th>
          <th>Timeout</th>
        </tr>
      </thead>  
      <tbody id="Filter1Table">
   -->
<?php
require_once('db.php');

 \header('Access-Control-Allow-Origin: *');
 \header('Content-Type: application/json; charset=utf-8'); 

 $pUser = $_GET['user'] ?? null;

if (\is_null($pUser) || $pUser === '') {
    header('HTTP/1.0 403 Forbidden');
    echo \json_encode(['e' => 'invalid ID given']);
}

$query = 'SELECT
    bfprojektstatus2.`PUSER` as userID, bfprojektstatus2.*, bfprojektstatus2.`AKTIV` as Aktiv
    FROM bfprojektstatus2
    WHERE  PUSER = "' . mysqli_real_escape_string($mysqli, $pUser) . '"';

$result = $mysqli->query($query);

$pcount = 0;

$data = [];
while ($row = $result->fetch_array()) {
    $data[] = [
        $userId => $row['userID'],
        $deviceId => (int)$row['ID'],
        $active => !!$row['Aktiv'],
        $error => !!$row['ERROR'],
        $timeout => !!$row['TIMEOUT'],
    ];

    echo "      <tr>\n\r";
    echo "          <td>" .$pcount  ."</td>\n\r";
    echo "          <td>" .$userId  ."</td>";
    echo "          <td>" .$deviceId  ."</td>\n\r";
    echo "        </tr>\n\r";
  } 
  echo "      </tbody>\n\r";
  echo "    </table>\n\r";
  echo "\n\r";

  $result->close();





// echo \json_encode($data);
// echo '</a><br>';
?>

<!-- <?php //Refresh it everytime.
$Rows = 4; //Dynamic number for Rowss
$Cols = 3; // Dynamic number for Colsumns
echo '<table border="1">';
for($i=1;$i<=$Rows;$i++){ echo  '<tr>';
  for($j=1;$j<=$Cols;$j++){ echo '<td>' . mt_rand($i, $i*100) . mt_rand($j, $j*100) . '</td>'; }
  echo '</tr>';
}
echo '</table>';
?> -->




<!-- 

$zeit = time() - 1000;

$query2 = 'SELECT
    bfccio.*
    FROM bfccio 
    WHERE  imei = "' . mysqli_real_escape_string($mysqli, $pUser) . '"';
 $result2 = $mysqli->query($query2);


echo $quary2;

//  $data2 = [];
 while ($row = $result2->fetch_array()) {
     $data2[] = [
         'imei' => $row['imei'],
         'iccid' => !!$row['iccid'],
         'csq' => !!$row['csq'],
         'pos' => !!$row['pos'],
         'ver' => !!$row['ver'],
         'DX' => !!$row['DX'],
         'AX' => !!$row['AX'],
         'text' => !!$row['text'],

     ];
 }
  echo \json_encode($data2);
 echo $data2;

//  while($zeile = $ergebnisimei->fetch_array())
//  $frage = "SELECT * FROM bfccio WHERE imei = '$merker' ORDER BY time DESC LIMIT 1" ;

//  echo $data2;
//  echo \json_encode($data2);


/// echo "<br>\n\r";

// $zeit = time() - 1000;
// $query1 = 'SELECT
//     bfccio.`ID`, bfccio.`time`,bfccio.`imei` as userIDD, bfccio.*
//     FROM bfccio
//     WHERE imei = "' . mysqli_real_escape_string($mysqli, $pUser) . '"';

// $result1 = $mysqli->query($query1);

// $data1 = [];
// while ($row = $result1->fetch_array()) {
//     if ($row['time'] != $time)
//     {
//       echo $time ."<br>\n\r";
//     }
//     if (($row['time']) > (time() - 600))
//     {
//       echo "<font color=\"red\">";
//     }

//     $data1[] = [
//         'time' => $row['time'],
//         'imei' => $row['imei'],
//         'iccid' => $row['iccid'],
        
//     ];
// }
// echo \json_encode($data1); -->