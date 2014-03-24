ifrApp.controller("DecisionCtrl", function ($scope, $http) {
  $http.get(ifr_api + 'decision?blog=2') // FIXME
    .success(function(data) {
      $scope.decisions = data.objects;
    });

  $scope.getDecision = function(entry) {
    for (var dIndex in $scope.decisions) {
      if ($scope.decisions[dIndex]['entry'] === entry) {
        return $scope.decisions[dIndex]['decision'];
      }
    }
    return null;
  };

  $scope.needsDecision = function(entry) {
    if ($scope.getDecision(entry) != null) {
      return false;
    }
    else {
      return true;
    }
  };

  $scope.submitDecision = function(formId, entry, decision) {
    console.log('form ' + formId + ' entry ' + entry);
    var formData = {"form": formId, "entry": entry, "decision": decision};
    console.dir(formData);
    $http.post(ifr_api + 'decision?blog=2', formData) // FIXME
      .success(function(data, status, headers, config) {
        console.log('success ' + status + " " + data);
        $scope.decisions.push(data); // FIXME: probably a better way
      })
      .error(function() {
        console.log('error');
      });
  };
});
