import './bootstrap'; // If you have a bootstrap.js for general JS setup
import '../css/app.css'; // If you have a main CSS file

import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './app'; // Assuming your main React App component is in App.tsx

// This is where your React app gets mounted to the DOM
const rootElement = document.getElementById('root');

if (rootElement) {
    const root = ReactDOM.createRoot(rootElement);
    root.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
} else {
    console.error('Root element with ID "root" not found in the document.');
}
