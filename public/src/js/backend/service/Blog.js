GSBackend.factory('Blog', function($resource){
    return $resource('/backend/blog/');
});