#!/bin/bash
# Enhanced Date/Time Picker Deployment Script
# Blue Motors Southampton - Tyre Booking System

echo "🛞 Blue Motors Enhanced Date/Time Picker Deployment"
echo "================================================="
echo ""

# Check if files exist
echo "📁 Checking file existence..."
if [ -f "enhanced-date-time-picker.js" ]; then
    echo "✅ enhanced-date-time-picker.js - Found"
else
    echo "❌ enhanced-date-time-picker.js - Missing"
fi

if [ -f "enhanced-date-time-picker.css" ]; then
    echo "✅ enhanced-date-time-picker.css - Found"
else
    echo "❌ enhanced-date-time-picker.css - Missing"
fi

if [ -f "tyre-datetime-integration.js" ]; then
    echo "✅ tyre-datetime-integration.js - Found"
else
    echo "❌ tyre-datetime-integration.js - Missing"
fi

echo ""
echo "🔍 Checking WordPress integration..."

# Check if main plugin file was modified
if grep -q "enhanced-datetime-picker" blue-motors-southampton.php; then
    echo "✅ WordPress enqueue statements added"
else
    echo "❌ WordPress integration missing"
fi

echo ""
echo "🧪 Testing file accessibility..."

# Test if files can be accessed via web
echo "📡 Testing file accessibility via HTTP..."
echo "Visit these URLs to verify files load:"
echo "- http://localhost:10010/wp-content/plugins/blue-motors-southampton/enhanced-date-time-picker.js"
echo "- http://localhost:10010/wp-content/plugins/blue-motors-southampton/enhanced-date-time-picker.css"
echo "- http://localhost:10010/wp-content/plugins/blue-motors-southampton/test-enhanced-datetime-picker.html"

echo ""
echo "🎯 Next Steps:"
echo "1. Test the functionality using the test page"
echo "2. Check your tyre booking page for the enhanced picker"
echo "3. Test on mobile devices"
echo "4. Verify AJAX time slot loading works"

echo ""
echo "📞 For support: Check the ENHANCED-DATETIME-PICKER-IMPLEMENTATION.md file"
echo "🐛 For debugging: Use debugTyreDateTimePicker() in browser console"
echo ""
echo "🎉 Deployment complete!"
