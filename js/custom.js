$(document).ready(function() {
    $(":input[name='key']").each(function(){
        $(this).val(hashkey);
    });

    $(".editbutton").click(function() {
        $(this).hide();
        $(".confirmbutton[id='"+this.hash+"']").show();
        $(".removebutton[id='"+this.hash+"']").show();
        $(".cancelbutton[id='"+this.hash+"']").show();

        function editable(field,hash){
            $(".input"+field+"[id='"+hash+"']").val($(".form"+field+"[id='"+hash+"']").html());
            $(".form"+field+"[id='"+hash+"']").hide();
            $(".input"+field+"[id='"+hash+"']").show();
        }
        function editableop(field,hash){
            value=$(".form"+field+"[id='"+hash+"']").html();
            splitted=value.split(", ");
            for (var i=0;i<splitted.length;i++){
                name=splitted[i];
            $(".input"+field+"[id='"+hash+"'] > option").each(function() {
                if (this.text === name) $(this).attr("selected",true);
                });
            }
            $(".form"+field+"[id='"+hash+"']").hide();
            $(".input"+field+"[id='"+hash+"']").show();
        }
        editable("date",this.hash);
        editable("item",this.hash);
        editable("price",this.hash);
        editable("comment",this.hash);
        editableop("payer",this.hash);
        editableop("user",this.hash);
    });

    $(".cancelbutton").click(function() {
        $(this).hide();
        $(".confirmbutton[id='"+this.hash+"']").hide();
        $(".removebutton[id='"+this.hash+"']").hide();
        $(".editbutton[id='"+this.hash+"']").show();

        function fixed(field,hash){
            $(".form"+field+"[id='"+hash+"']").show();
            $(".input"+field+"[id='"+hash+"']").hide();
        }
        fixed("date",this.hash);
        fixed("item",this.hash);
        fixed("price",this.hash);
        fixed("comment",this.hash);
        fixed("payer",this.hash);
        fixed("user",this.hash);
    });

    $(".confirmbutton").click(function() {
        $("form[id='"+this.hash+"']").submit();
    });
    $(".removebutton").click(function() {
        $(".postaction[id='"+this.hash+"']").val("remove");
        $("form[id='"+this.hash+"']").submit();
    });

    $(".addbutton").click(function() {
        $("#addform").submit();
    });

    $(".monthbutton").click(function() {
        $("form[id='"+this.id+"']").submit();
    });
});
