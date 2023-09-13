<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="<?php echo base_url();?>/assets/jquery/jquery-3.6.4.min.js"></script>

</head>
<body>
<button onclick="send()">OK</button>

<script>
    function send(){

        $.ajax({
           url:"<?php echo url_to('/json'); ?>",
            method:"POST",
            data:{user:"Allen"},
            success:function(data){
               console.log(data);
            },
            error: function(xhr, desc, err){
                console.log(err);
            }
        });

    }

</script>
</body>
</html>