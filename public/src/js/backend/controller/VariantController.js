GSBackend.controller('VariantController', ['$scope', '$routeParams', '$resource', '$location', 'Variant', function($scope, $routeParams, $resource, $location, Variant) {
    $scope.name = "VariantController";

    $scope.saveVariant = function() {
        Variant.save($scope.variant, function() {
            gsAlert('success', 'Variante', 'Die Variante wurde erfolgreich gespeichert');
            $location.path('/products/' + $routeParams.productId);
        })
    };

    if($routeParams.variantId == 'add') {
        $scope.title = 'Neue Variante anlegen';
        $scope.variant = {
            productID: $routeParams.productId
        };
    } else {
        $scope.title = 'Variante bearbeiten';
        $scope.variant = Variant.get({
            id: $routeParams.variantId
        });
    }
}]);