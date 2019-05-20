<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap4.min.css">
    <title>PHP CRUD</title>
</head>
<body>
<header class="rounded bg-dark p-3 text-warning m-3">
    <h2>PHP CRUD</h2>
</header>

<div class="container">
    <h2>DataTables</h2>
    <br><br>
    <table class="table table-hover table-bordered" id="example">
        <thead>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Age</th>
            <th>Payment</th>
            <th>Action</th>
        </tr>
        </thead>
    </table>    
</div>


<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap4.min.js"></script>
<script>

    $(document).ready(function(){
        var dataTable=$('#example').DataTable({
            "processing": true,
            "serverSide":true,
            "ajax":{
                url:"usersdata.php",
                type:"post"
            }
        });
    });
</script>
</body>
</html>
