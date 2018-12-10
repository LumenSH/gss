GSBackend.filter('germanDate', function() {
    return function(input) {
        var int = parseInt(input);
        var date = new Date(int * 1000);

        var day = (date.getDate()).toString();
        var month = (date.getMonth() + 1).toString();
        var year = (date.getYear() + 1900).toString();

        var hour = date.getHours().toString();
        var minute = date.getMinutes().toString();
        var seconds = date.getSeconds().toString();

        if(day.length == 1) { day = "0" + day; }
        if(month.length == 1) { month = "0" + month; }
        if(hour.length == 1) { hour = "0" + hour; }
        if(minute.length == 1) { minute = "0" + minute; }
        if(seconds.length == 1) { seconds = "0" + seconds; }

        return day + "." + month + "." + year + " " + hour + ":" + minute + ":" + seconds;
    };
});