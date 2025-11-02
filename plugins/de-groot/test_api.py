#!/usr/bin/env python3
"""
Test script to verify de-groot API integration
"""

import requests
import json

def test_de_groot_api():
    """Test the de-groot API with the provided credentials"""
    
    # API configuration
    base_url = "https://api.grootsgedrukt.nl"
    token = "n2thj#FpylfBC9Hpu5VuYHNU6DRNCiiGJ3N"
    
    headers = {
        "accept": "*/*",
        "accept-language": "en-US,en;q=0.9",
        "authorization": f"Bearer {token}",
        "cache-control": "no-cache",
        "pragma": "no-cache"
    }
    
    print("🔍 Testing de-groot API integration...")
    print(f"📍 Base URL: {base_url}")
    print(f"🔑 Token: {token[:10]}...")
    
    try:
        # Test 1: Get all articles (categories)
        print("\n📋 Test 1: Fetching all articles (categories)...")
        url = f"{base_url}/v1/articles"
        response = requests.get(url, headers=headers, timeout=30)
        
        if response.status_code == 200:
            articles = response.json()
            print(f"✅ Success! Found {len(articles)} articles")
            
            if articles:
                # Show first few articles as examples
                print("\n📄 Sample articles:")
                for i, article in enumerate(articles[:3]):
                    print(f"  {i+1}. {article.get('articlename', 'N/A')} (ID: {article.get('articlenumber', 'N/A')})")
                
                # Test 2: Get options for first article
                if articles:
                    first_article = articles[0]
                    articlenumber = first_article.get('articlenumber')
                    
                    if articlenumber:
                        print(f"\n⚙️  Test 2: Fetching options for article '{articlenumber}'...")
                        options_url = f"{base_url}/v1/articles"
                        params = {"options": "null", "articlenumber": articlenumber}
                        
                        options_response = requests.get(options_url, params=params, headers=headers, timeout=60)
                        
                        if options_response.status_code == 200:
                            options = options_response.json()
                            print(f"✅ Success! Found {len(options)} option groups")
                            
                            # Show sample options
                            for i, option_group in enumerate(options[:2]):
                                print(f"  Option Group {i+1}: {option_group.get('description', 'N/A')}")
                                values = option_group.get('values', [])
                                if isinstance(values, list) and values:
                                    print(f"    - {len(values)} values available")
                                elif isinstance(values, dict):
                                    print(f"    - {len(values)} values available")
                        else:
                            print(f"❌ Failed to fetch options: {options_response.status_code} - {options_response.text}")
                        
                        # Test 3: Get single article details
                        print(f"\n📄 Test 3: Fetching single article details for '{articlenumber}'...")
                        single_article_params = {"article": "null", "articlenumber": articlenumber}
                        
                        single_article_response = requests.get(url, params=single_article_params, headers=headers, timeout=30)
                        
                        if single_article_response.status_code == 200:
                            single_article = single_article_response.json()
                            print(f"✅ Success! Found article details")
                            print(f"  - Article name: {single_article.get('articlename', 'N/A')}")
                            print(f"  - Article number: {single_article.get('articlenumber', 'N/A')}")
                            print(f"  - Last edit: {single_article.get('lastEdit', 'N/A')}")
                        else:
                            print(f"❌ Failed to fetch single article: {single_article_response.status_code} - {single_article_response.text}")
                        
                        # Test 4: Get products for first article
                        print(f"\n🏷️  Test 4: Fetching products for article '{articlenumber}'...")
                        products_params = {"products": "null", "articlenumber": articlenumber}
                        
                        products_response = requests.get(url, params=products_params, headers=headers, timeout=120)
                        
                        if products_response.status_code == 200:
                            products = products_response.json()
                            print(f"✅ Success! Found product tree")
                            print(f"  - Product structure: {type(products).__name__}")
                            if isinstance(products, dict):
                                print(f"  - Keys: {list(products.keys())[:5]}")
                        else:
                            print(f"❌ Failed to fetch products: {products_response.status_code} - {products_response.text}")
            else:
                print("⚠️  No articles found")
        else:
            print(f"❌ Failed to fetch articles: {response.status_code}")
            print(f"Response: {response.text}")
            
    except requests.RequestException as e:
        print(f"❌ Request failed: {str(e)}")
    except Exception as e:
        print(f"❌ Unexpected error: {str(e)}")

if __name__ == "__main__":
    test_de_groot_api()
