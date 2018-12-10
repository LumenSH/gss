GSBackend.controller('CmsController', ['$scope', '$routeParams', '$location', 'Cms', function($scope, $routeParams, $location, Cms) {
    $scope.name = "CmsController";
    $scope.$tab = 'de';
    $scope.editorOptions = {
        lineWrapping : true,
        lineNumbers: true,
        mode: 'htmlmixed'
    };

    $scope.save = function () {
        $scope.cms.id = $routeParams.cmsId;
        Cms.save($routeParams.cmsId, $scope.cms);
        $location.path('/cms');
    };

    $scope.editCms = function (cms) {
        $location.path('/cms/' + cms.id);
    };

    $scope.deleteCms = function (cms) {
        console.log(cms);
        Cms.delete({
            id: cms.id
        }, function() {
            $scope.refresh();
        });
    };

    $scope.refresh = function () {
        $scope.cms = Cms.get();
    };

    if(typeof $routeParams.cmsId == 'undefined') {
        $scope.refresh();
    } else {
        if($routeParams.cmsId != 'add') {
            $scope.cms = Cms.get({
                id: $routeParams.cmsId
            });
            $scope.title = 'Seite bearbeiten';
        } else {
            $scope.title = 'Seite erstellen';
        }
    }
}]);




