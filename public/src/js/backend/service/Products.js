GSBackend.factory('Products', function($resource){
    return $resource('/backend/products/');
});