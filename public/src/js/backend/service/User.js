GSBackend.factory('User', function($resource){
    return $resource('/backend/user/');
});