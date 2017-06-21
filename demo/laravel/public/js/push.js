function createChat() {
    this.ws = new WebSocket("ws://127.0.0.1:9501");

    this.wsOpen();
    this.wsMessage();
    this.wsOnclose();
    this.wsOnerror();
}

createChat.prototype = {
    wsSend : function(data){
        this.ws.send(data);

        console.log(this.tt);
    },
    wsOpen : function (){
        var the = this;

        this.ws.onopen = function( event ){
            console.log("ws is open ");

            /*setTimeout(function () {
                the.addMessage("async",1);
            },1000);*/

            /*a.forEach(function (member) {
                member.addMessage("publish",1);
            })*/
        }
    },
    addMessage:function ($type,$data) {
        var a =  {
            'type':$type,
            'data':$data
        };
        this.wsSend(JSON.stringify(a));
    },
    wsMessage : function(){
        this.ws.onmessage=function(event){
            //var d =JSON.parse(event.data);
            console.log(event.data);
        }
    },
    wsOnclose : function(){
        var the = this;
        this.ws.onclose = function(event){

        }
    },
    wsOnerror : function(){
        this.ws.onerror = function(event){
            alert("ws open error");
        }
    }
};

new createChat()

/*
var a = new Array();
a.push(new createChat());
a.push(new createChat());
*/
