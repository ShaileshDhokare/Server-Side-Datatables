<?php
$conn = new mysqli('localhost', 'root', '', 'sd_test');

$col = array(
    0   =>  'id',
    1   =>  'first_name',
    2   =>  'last_name',
    3   =>  'email',
    4   =>  'age',
    5   =>  'payment'
);

$request = $_REQUEST;

$sql = "SELECT * FROM `users`";

$query = mysqli_query($conn, $sql);

$totalData = mysqli_num_rows($query);

$totalFilter = $totalData;

//Search
$sql = "SELECT * FROM users WHERE 1=1";
if (!empty($request['search']['value'])) {
    $sql .= " AND id Like '" . $request['search']['value'] . "%' ";
    $sql .= " OR first_name Like '" . $request['search']['value'] . "%' ";
    $sql .= " OR last_name Like '" . $request['search']['value'] . "%' ";
    $sql .= " OR email Like '" . $request['search']['value'] . "%' ";
    $sql .= " OR age Like '" . $request['search']['value'] . "%' ";
    $sql .= " OR payment Like '" . $request['search']['value'] . "%' ";
}


//Order
$sql .= " ORDER BY " . $col[$request['order'][0]['column']] . "   " . $request['order'][0]['dir'] . "  LIMIT " .
    $request['start'] . "  ," . $request['length'] . "  ";


$query = mysqli_query($conn, $sql);
$totalData = mysqli_num_rows($query);


$data = array();
while ($row = mysqli_fetch_array($query)) {
    $subdata = array();
    $subdata[] = $row[0];
    $subdata[] = $row[1];
    $subdata[] = $row[2];
    $subdata[] = $row[3];
    $subdata[] = $row[4];
    $subdata[] = $row[5];
    $subdata[] = "<a class='btn btn-sm btn-primary' href='/edit/$row[0]' role='button'>Edit</button>";
    $data[] = $subdata;
}

$json_data = array(
    "draw"              =>  intval($request['draw']),
    "recordsTotal"      =>  intval($totalData),
    "recordsFiltered"   =>  intval($totalFilter),
    "data"              =>  $data
);

echo json_encode($json_data);
