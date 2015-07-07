<script src="http://code.jquery.com/jquery-latest.min.js"
        type="text/javascript"></script>
<script >
    $(document).ready(function(){
        $(".vote-stars ol li").on("click", function() {
            
                $(this).siblings().removeClass("yellow");
                var index = $(this).index();
                
                $(this).parent().children().each(function(i) {
                    $(this).addClass("yellow");
                    if (i == index)
                    {
                        return false;
                    }
                });
        });
    });
</script>        
    
<style>
    .vote-stars {}
    .vote-stars ol { margin: 0; padding: 0; }
    .vote-stars ol li {
        margin: 0 7px 0; padding: 0; cursor: pointer; display: inline-block; height: 15px; width: 18px; 
        background: rgba(0, 0, 0, 0) url("star.png") no-repeat scroll 0 top; 
    }
    .vote-stars ol li.yellow {
        background: rgba(0, 0, 0, 0) url("star.png") no-repeat scroll 0 bottom; 
    }
</style>


<div class="vote-stars">
    <ol>
        <li class="yellow"></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    <ol>
</div>
