import http from 'k6/http';
import { sleep } from 'k6';

export let options = {
  vus: 500,               // 50 Virtual Users (VUs)
  duration: '2m',        // Run the test for 5 minutes
};

export default function () {
  http.get('http://localhost/health-tracker'); // Replace with your local website's URL
  sleep(1);  // Simulate 1-second wait time between requests
}
