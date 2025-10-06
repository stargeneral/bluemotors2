# Official DVLA API Tests - Based on Official Documentation
# https://developer-portal.driver-vehicle-licensing.api.gov.uk/

Write-Host "🔧 Official DVLA API Test Suite" -ForegroundColor Cyan
Write-Host "===============================" -ForegroundColor Cyan
Write-Host "Based on official DVLA documentation" -ForegroundColor Gray
Write-Host ""

# Replace with your actual DVLA API key
$dvlaApiKey = "w2unkuUU9hapP9C8P7x2R62kZ5GNTtYu4MGfLpQj"

if ($dvlaApiKey -eq "YOUR_DVLA_API_KEY_HERE") {
    Write-Host "⚠️ IMPORTANT: Replace YOUR_DVLA_API_KEY_HERE with your actual API key" -ForegroundColor Yellow
    Write-Host ""
}

# Common headers for DVLA API
$headers = @{
    'x-api-key' = $dvlaApiKey
    'Content-Type' = 'application/json'
}

# Test VRN from DVLA documentation that should return a 400 error
$testVrn = "ER19BAD"  # Official test VRN that returns 400 Bad Request

# Official test VRN for successful response
$validTestVrn = "TE57VRN"  # From documentation example

Write-Host "==========================================" -ForegroundColor Gray
Write-Host "TEST 1: DVLA PRODUCTION API" -ForegroundColor Yellow
Write-Host "Endpoint: https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles" -ForegroundColor Gray
Write-Host "Testing with official test VRN: $testVrn (should return 400 Bad Request)" -ForegroundColor Gray
Write-Host ""

$productionUrl = "https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles"
$body = @{ registrationNumber = $testVrn } | ConvertTo-Json

try {
    $startTime = Get-Date
    $response = Invoke-WebRequest -Uri $productionUrl -Method POST -Headers $headers -Body $body -TimeoutSec 15
    $endTime = Get-Date
    $responseTime = ($endTime - $startTime).TotalMilliseconds
    
    Write-Host "✅ PRODUCTION API SUCCESS: HTTP $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
    Write-Host "Response Body: $($response.Content)" -ForegroundColor Gray
    
} catch {
    $endTime = Get-Date
    $responseTime = ($endTime - $startTime).TotalMilliseconds
    
    # Check if it's a 400 error (expected for test VRN)
    if ($_.Exception.Response.StatusCode -eq 400) {
        Write-Host "✅ PRODUCTION API WORKING: HTTP 400 (Expected for test VRN '$testVrn')" -ForegroundColor Green
        Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
        Write-Host "✅ This proves DVLA Production API is accessible!" -ForegroundColor Green
    } else {
        Write-Host "❌ PRODUCTION API BLOCKED: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
        Write-Host "🚨 StartTLD is blocking DVLA Production API!" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Gray
Write-Host "TEST 2: DVLA UAT (TEST) ENVIRONMENT" -ForegroundColor Yellow
Write-Host "Endpoint: https://uat.driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles" -ForegroundColor Gray
Write-Host "Testing with same test VRN: $testVrn (should return 400 Bad Request)" -ForegroundColor Gray
Write-Host ""

$uatUrl = "https://uat.driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles"

try {
    $startTime = Get-Date
    $response = Invoke-WebRequest -Uri $uatUrl -Method POST -Headers $headers -Body $body -TimeoutSec 15
    $endTime = Get-Date
    $responseTime = ($endTime - $startTime).TotalMilliseconds
    
    Write-Host "✅ UAT API SUCCESS: HTTP $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
    Write-Host "Response Body: $($response.Content)" -ForegroundColor Gray
    
} catch {
    $endTime = Get-Date
    $responseTime = ($endTime - $startTime).TotalMilliseconds
    
    # Check if it's a 400 error (expected for test VRN)
    if ($_.Exception.Response.StatusCode -eq 400) {
        Write-Host "✅ UAT API WORKING: HTTP 400 (Expected for test VRN '$testVrn')" -ForegroundColor Green
        Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
        Write-Host "✅ This proves DVLA UAT API is accessible!" -ForegroundColor Green
    } else {
        Write-Host "❌ UAT API BLOCKED: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
        Write-Host "🚨 StartTLD is also blocking DVLA UAT environment!" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Gray
Write-Host "TEST 3: DVSA API (COMPARISON)" -ForegroundColor Yellow
Write-Host "Endpoint: https://history.mot.api.gov.uk" -ForegroundColor Gray
Write-Host ""

try {
    $startTime = Get-Date
    $response = Invoke-WebRequest -Uri "https://history.mot.api.gov.uk" -Method HEAD -TimeoutSec 10
    $endTime = Get-Date
    $responseTime = ($endTime - $startTime).TotalMilliseconds
    
    Write-Host "✅ DVSA API SUCCESS: HTTP $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
    Write-Host "✅ This proves government APIs CAN work from your server!" -ForegroundColor Green
    
} catch {
    $endTime = Get-Date
    $responseTime = ($endTime - $startTime).TotalMilliseconds
    
    Write-Host "❌ DVSA API FAILED: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Response Time: $([math]::Round($responseTime, 2))ms" -ForegroundColor Gray
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Gray
Write-Host "📋 ANALYSIS FOR STARTTLD" -ForegroundColor Cyan
Write-Host ""

Write-Host "Expected Results if StartTLD is blocking DVLA:" -ForegroundColor White
Write-Host "❌ DVLA Production: Connection timeout/error" -ForegroundColor Red
Write-Host "❌ DVLA UAT: Connection timeout/error" -ForegroundColor Red  
Write-Host "✅ DVSA API: HTTP 403/404 (works)" -ForegroundColor Green
Write-Host ""
Write-Host "If DVLA APIs fail but DVSA works = SELECTIVE BLOCKING PROOF" -ForegroundColor Yellow
Write-Host ""

# Additional quick connectivity tests
Write-Host "==========================================" -ForegroundColor Gray
Write-Host "BONUS: Quick Connectivity Tests" -ForegroundColor Cyan
Write-Host ""

$testEndpoints = @(
    @{ Name = "DVLA Production"; Url = "https://driver-vehicle-licensing.api.gov.uk" },
    @{ Name = "DVLA UAT"; Url = "https://uat.driver-vehicle-licensing.api.gov.uk" },
    @{ Name = "DVSA API"; Url = "https://history.mot.api.gov.uk" },
    @{ Name = "Gov.uk (Control)"; Url = "https://www.gov.uk" }
)

foreach ($endpoint in $testEndpoints) {
    try {
        $start = Get-Date
        $response = Invoke-WebRequest -Uri $endpoint.Url -Method HEAD -TimeoutSec 5
        $time = ((Get-Date) - $start).TotalMilliseconds
        Write-Host "✅ $($endpoint.Name): HTTP $($response.StatusCode) ($([math]::Round($time, 0))ms)" -ForegroundColor Green
    } catch {
        $time = ((Get-Date) - $start).TotalMilliseconds
        Write-Host "❌ $($endpoint.Name): BLOCKED ($([math]::Round($time, 0))ms)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "🎯 KEY EVIDENCE FOR STARTTLD:" -ForegroundColor Cyan
Write-Host "• If BOTH DVLA Production AND UAT fail = Systematic DVLA blocking" -ForegroundColor Yellow
Write-Host "• If DVSA works but DVLA doesn't = Selective government API blocking" -ForegroundColor Yellow
;
Write-Host "• Official DVLA test VRN should return HTTP 400, not connection error" -ForegroundColor Yellow