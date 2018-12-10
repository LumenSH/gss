GSBackend.controller('GameserverController', ['$scope', '$routeParams', '$location', 'Gameserver', function($scope, $routeParams, $location, Gameserver) {
    $scope.name = "GameserverController";
    $scope.page = 1;

    $.get(GS.Config.baseUrl + 'backend/server/getOptions', function(data) {
        $scope.options = data;
    }, 'json');

    $scope.refreshGameserver = function() {
        $scope.gameservers = Gameserver.get({
            search: $scope.search,
            page: $scope.page
        });
    };

    $scope.loadPage = function(page) {
        $scope.page = page;
        $scope.refreshGameserver();
    };

    $scope.refreshGameserver();
}]);