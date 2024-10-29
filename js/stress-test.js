import http from 'k6/http';
import { sleep } from 'k6';

export let options = {
  stages: [
    { duration: '1m', target: 100 },  // ramp-up to 50 users
    { duration: '2m', target: 100 },  // stay at 50 users
    { duration: '1m', target: 800 }, // ramp-up to 100 users
    { duration: '2m', target: 800 }, // stay at 100 users
    { duration: '1m', target: 0 },   // ramp-down to 0 users
  ],
};

export default function () {
  http.get('http://localhost/health-tracker');  // URL of your localhost website
  sleep(1);
}
