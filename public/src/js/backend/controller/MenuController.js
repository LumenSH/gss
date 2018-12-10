GSBackend.controller('MenuController', ['$scope', '$routeParams', '$resource', '$location', 'Menu', function($scope, $routeParams, $resource, $location, Menu) {
    $scope.name = "MenuController";

    $scope.editMenu = function(menu) {
        $location.path('/menu/' + menu.id);
    };

    $scope.menuItems = [];
    $scope.mode = 0;
    $scope.menu = {
        menuTyp: 2
    };

    $scope.loadData = function(mode) {
        var menu = [];

        $scope.menuData.data.forEach(function(item) {
            if(item.menuTyp == mode) {
                menu.push(item);
            }
        });

        $scope.menuItems = menu;
    };

    $scope.saveMenu = function() {
        Menu.save($scope.menu, function() {
            toastr.success('Menupunkt wurde erfolgreich gespeichert');
            $location.path('/menu');
        });
    };

    $scope.deleteMenu = function(item) {
        $scope.menuData.data.splice($scope.menuData.data.indexOf(item), 1);
        $scope.loadData(item.menuTyp);

        Menu.delete({
            id: item.id
        });
    };

    if($routeParams.menuId) {
        if(parseInt($routeParams.menuId)) {
            $scope.menu = Menu.get({
                id: $routeParams.menuId
            });
        }
    } else {
        $scope.menuData = Menu.get(function() {
            $scope.loadData(0);
        });
    }
}]);