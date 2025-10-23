import React, { useState, useEffect } from 'react';

const TestModeWarning = (props) => {
    const { is_test_mode } = props

    return (
        is_test_mode && <p className="antom-test-mode-warning">run in antom test mode</p>
    )
}

export default TestModeWarning;