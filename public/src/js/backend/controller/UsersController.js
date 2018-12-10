GSBackend.controller('UsersController', ['$scope', '$routeParams', '$resource', '$location', 'User', function($scope, $routeParams, $resource, $location, User) {
    $scope.name = "UsersController";
    $scope.page = 1;

    $scope.editUser = function(user) {
        $location.path('/users/' + user.id);
    };

    $scope.search = function() {
        if(typeof $scope.searchValue == 'undefined') {
            $scope.searchValue = '';
        }

        var searchValue = "%" + $scope.searchValue +  "%";
        if(searchValue.length == 2) {
            $scope.users = User.get({
                page: $scope.page
            });
        } else {
            $scope.users = User.get({
                search: [{
                    Username: searchValue,
                    Email: searchValue
                }],
                page: $scope.page
            });
        }
    };

    $scope.loadPage = function(page) {
        $scope.page = page;
        $scope.search();
    };

    $scope.users = User.get();
}]);