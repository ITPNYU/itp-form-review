ifrApp.controller("DecisionCtrl", function ($scope) {
  $scope.decisions = {"12": "yes"}; // FIXME: implement

  $scope.getDecision = function(id) {
    return $scope.decisions[id];
  };

  $scope.needsDecision = function(id) {
    if ($scope.getDecision(id) != null) {
      return false;
    }
    else {
      return true;
    }
  };
});
