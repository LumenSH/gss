GSBackend.controller('SupportController', ["$scope", "$route", "$routeParams", "$location", "Support", function($scope, $route, $routeParams, $location, Support) {
    $scope.$route = $route;
    $scope.$location = $location;
    $scope.$routeParams = $routeParams;

    $scope.editorOptions = {
        lineWrapping : true,
        lineNumbers: true,
        mode: 'htmlmixed'
    };

    $scope.options = {};
    $scope.search = {
        folder: "0",
        type: {"id":-1,"name":"All"},
        game: {"id":-1,"name":"All"}
    };
    $scope.offset = 0;

    $scope.openTicket = function(ticket) {
        $location.path('/support/' + ticket.id);
    };

    $scope.closeTicket = function(ticket) {
        $.get(GS.Config.baseUrl + 'backend/support/closeTicket/' + ticket.id, function(data) {
            gsAlert('success', 'Support', 'Ticket has been closed');
            $scope.refreshTickets();
        });
    };

    $scope.refreshTickets = function() {
        $scope.tickets = Support.get({
            search: $scope.search,
            page: $scope.offset
        });
    };

    $scope.answerTicket = function() {
        $.post(GS.Config.baseUrl + 'backend/support/answerTicket/', {ticketID: $routeParams.ticketId, answer: $scope.message}, function() {
            $route.reload();
        });
    };

    $scope.toCustomer = function(ticket) {
        $location.path('/users/' + ticket.userID);
    };

    $scope.toGameserver = function(ticket) {
        $location.path('/gameserver/' + ticket.gameserverID);
    };

    $scope.loadPage = function (page) {
        $scope.offset = page;
        $scope.refreshTickets();
    };

    $.get(GS.Config.baseUrl + 'backend/support/getOptions', function(data) {
        $scope.options = data;
    }, 'json');

    if(typeof $routeParams.ticketId == 'undefined') {
        $scope.refreshTickets();
    } else {
        $scope.ticket = Support.get({
            id: $routeParams.ticketId
        });
    }
}]);