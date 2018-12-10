GSBackend.controller('UserController', ['$scope', '$routeParams', 'User', 'GPHistorie', '$http', function($scope, $routeParams, User, GPHistorie, $http) {
    $scope.name = "UserController";

    var user = User.get({
        id: $routeParams.userId
    }, function() {
        user.Password = '';
    });

    $scope.gphistory = GPHistorie.get({
        userID: $routeParams.userId
    });

    $http.get('/backend/user/servers?userID=' + $routeParams.userId).then(function (response) {
        $scope.servers = response.data.data;
    });

    $scope.saveUser = function() {
        User.save($scope.user, function() {
            toastr.success('Kunde wurde erfolgreich gespeichert');
        });
    };

    $scope.sendMessage = function () {
        $http.post('/backend/user/sendMessage', {
            userId: $routeParams.userId,
            subject: $scope.subject,
            message: $scope.message
        }).then(function (response) {
            toastr.success('Mail wurde versendet');
        });
    };

    $scope.user = user;
    $scope.$tab = 'detail';
}]);