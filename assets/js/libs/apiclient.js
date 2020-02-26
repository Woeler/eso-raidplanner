import axios from 'axios';

export default axios.create({
    'baseUrl': window.location.host,
    timeout: 3000
});