GSBackend.factory('Gameserver', function($resource){
    return $resource('/backend/server/');
});