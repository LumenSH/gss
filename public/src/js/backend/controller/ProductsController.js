GSBackend.controller('ProductsController', ['$scope', '$routeParams', '$resource', '$location', '$http', 'Products', 'Version', 'Variant', function($scope, $routeParams, $resource, $location, $http, Products, Version, Variant) {
    $scope.name = "ProductsController";

    $scope.editorOptions = {
        lineWrapping : true,
        lineNumbers: true,
        mode: 'htmlmixed'
    };

    $scope.saveProduct = function() {
        delete $scope.product.variants;
        delete $scope.product.versions;
        delete $scope.product.newVersion;
        Products.save($scope.product, function (response) {
            if(typeof $scope.uploadForm != 'undefined') {
                $scope.uploadForm.append('id', response.id);

                $http.post('backend/products/saveImage', $scope.uploadForm, {
                    withCredentials: true,
                    headers: {'Content-Type': undefined },
                    transformRequest: angular.identity
                }).then(function() {
                    gsAlert('success', 'Produkt-Verwaltung', 'Das Produkt wurde erfolgreich gespeichert');
                    $location.path('/products');
                });
            } else {
                gsAlert('success', 'Produkt-Verwaltung', 'Das Produkt wurde erfolgreich gespeichert');
                $location.path('/products');
            }
        });
    };

    $scope.deleteProduct = function (product) {
        $scope.products.data.splice($scope.products.data.indexOf(product), 1);

        Products.delete({
            id: product.id
        });
    };

    $scope.addVersion = function() {
        var version = $scope.newVersion;
        Version.save({
            productID: $routeParams.productId,
            version: version
        }, function(response) {
            $scope.product.versions.push(response.data);
            $scope.newVersion = '';
        });
    };

    $scope.uploadFile = function(files) {
        $scope.uploadForm = new FormData();

        $scope.uploadForm.append("img", files[0]);
    };

    $scope.deleteVersion = function(version) {
        Version.delete(version);
        $scope.product.versions.splice($scope.product.versions.indexOf(version), 1);
    };

    $scope.deleteVariant = function(variant) {
        Variant.delete({
            id: variant.id
        });
        $scope.product.variants.splice($scope.product.variants.indexOf(variant), 1);
    };

    if(typeof $routeParams.productId == 'undefined') {
        $scope.products = Products.get();
    } else {
        if($routeParams.productId == 'add') {
            $scope.panelTitle = 'Neues Produkt';
            $scope.tab = 'allgemein';
        } else {
            $scope.tab = 'allgemein';
            $scope.product = Products.get({
                id: $routeParams.productId
            }, function() {
                $scope.panelTitle = $scope.product.name + ' bearbeiten';
            });
        }
    }
}]);