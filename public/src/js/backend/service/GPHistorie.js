GSBackend.factory('GPHistorie', function($resource){
    return $resource('/backend/user/getGPHistory');
});