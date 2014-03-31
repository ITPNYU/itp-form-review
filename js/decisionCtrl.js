ifrApp.controller("DecisionCtrl", ['$scope', '$http', '$window', 'underscore', function ($scope, $http, $window, _) {
  $http.get(ifr_api + 'decision?blog=2') // FIXME
    .success(function(data) {
      $scope.decisions = data.objects;
    });

  $scope.getDecision = function(entry) {
    for (var dIndex in $scope.decisions) {
      if ($scope.decisions[dIndex]['entry'] == entry) {
        return $scope.decisions[dIndex]['decision'];
      }
    }
    return null;
  };

  $scope.needsDecision = function(entry) {
    if ($scope.getDecision(entry) != null) {
      //console.log('entry ' + entry + ' has decision ' + $scope.getDecision(entry));
      return false;
    }
    else {
      return true;
    }
  };

  $scope.submitDecision = function(formId, entry, decision, fname, lname, email, affiliation, date_created) {
    console.log('form ' + formId + ' entry ' + entry);
    var discount = 0.0;
    var month = parseInt(date_created.substr(5,2));
    var mday = parseInt(date_created.substr(8,2));
    console.log("application date is " + month + "/" + mday);
    
    if (decision === 'comp') {
      discount = 1.0;
    }
    else {
      if (affiliation['ITP Alumni'] != null) {
        discount = discount + 0.5;
      }
      if (affiliation['ITP Camp Alumni'] != null) {
        discount = discount + 0.25;
      }
      if ((month < 4) || (month === 4 && mday <= 16)) {
        discount = discount + 0.25;
      }
    }
    
    var formData = {
      "form": formId,
      "entry": entry,
      "decision": decision,
      "fname": fname,
      "lname": lname,
      "email": email,
      "discount": discount
    };
    console.dir(formData);
    $http.post(ifr_api + 'decision?blog=2', formData) // FIXME
      .success(function(data, status, headers, config) {
        console.log('success ' + status + " " + data);
        $scope.decisions.push(data); // FIXME: probably a better way
        var messageData = {
          "firstName": formData["fname"],
          "registerLink": "https://itp.nyu.edu/camp/2014/register/?email=" + escape(formData["email"])
        };
        $window.open('mailto:' + email + '?subject=ITP%20Camp%20Application%20Status&body=' + renderMessage(ifr_decision_message[formData["decision"]], messageData));
      })
      .error(function() {
        console.log('error');
      });
  };
}]);
