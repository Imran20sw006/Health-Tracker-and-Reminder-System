import http from 'k6/http';
import { sleep } from 'k6';

export let options = {
  stages: [
    { duration: '10s', target: 100 },   // Ramp up to 10 users
    { duration: '10s', target: 1000 },  // Spike to 100 users
    { duration: '10s', target: 10 },   // Drop back to 10 users
    { duration: '10s', target: 0 },    // Ramp down to 0 users
  ],
};

export default function () {
  http.get('http://localhost/health-tracker');  // Replace with your website's URL
  sleep(1);
}
