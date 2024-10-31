

(function() {
  // only from Moodle v4.3
  if (typeof moodleBranch !== 'undefined' && moodleBranch >= 403) {
    /*
       Some Moodle ajax functionality do not give us to use hooks or inlcude our custom code
       Next code is used to catch, change the request and send again fo special cases
       External function 'block_exacomp_fakecore_get_fragment' will be called instead of 'core_get_fragment'
       (Look also externallib.php function 'fakecore_get_fragment')
     */
    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function (method, url, ...rest) {
      this._method = method; // Save the request method for later use in `send`

      // Check if URL has `info=core_get_fragment` (for GET requests)
      if (method.toUpperCase() === 'GET' && url.includes('service.php') && url.includes('info=core_get_fragment')) {
        url = url.replace('info=core_get_fragment', 'info=block_exacomp_fakecore_get_fragment');
      }

      // Call the original `open` function with the modified URL
      return originalOpen.apply(this, [method, url, ...rest]);
    };

    XMLHttpRequest.prototype.send = function (body) {
      // Check if it's a POST request and the body contains `info : core_get_fragment`
      if (this._method.toUpperCase() === 'POST' && body && typeof body === 'string') {
        let bodyArr = JSON.parse(body);
        if (bodyArr[0] && bodyArr[0].methodname && bodyArr[0].methodname == 'core_get_fragment') {
          bodyArr[0].methodname = 'block_exacomp_fakecore_get_fragment';
          body = JSON.stringify(bodyArr);
        }
      }

      // Call the original `send` function with the modified body
      return originalSend.call(this, body);
    };
  }

})();

