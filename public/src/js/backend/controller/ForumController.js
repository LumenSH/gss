GSBackend.controller('ForumController', ['$scope', '$routeParams', '$location', 'Forum', function($scope, $routeParams, $location, Forum) {
    $scope.name = "ForumController";

    $scope.editForum = function(forum) {
        $location.path('forum/' + forum.id);
    };

    $scope.deleteForum = function(forum) {
        Forum.delete({
            id: forum.id
        }, function() {
            $scope.forum = Forum.get();
        });
    };

    $scope.saveForum = function() {
        Forum.save($scope.forum, function () {
            $location.path('forum');
        });
    };

    if(typeof $routeParams.forumId == 'undefined') {
        $scope.forum = Forum.get();
    } else {
        $scope.forum = Forum.get({
            id: $routeParams.forumId
        });
    }
}]);